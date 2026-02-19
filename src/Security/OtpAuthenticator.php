<?php

namespace App\Security;

use App\DTO\ApplicantStatusEnum;
use App\Exception\MfcApiException;
use App\Form\OtpType;
use App\Service\Cache\EmailOtpStorageService;
use App\Service\MfcApiClientInterface;
use App\Service\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class OtpAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login_otp';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EmailOtpStorageService $emailOtpStorageService,
        private readonly FormFactoryInterface $formFactory,
        private readonly UserService $userService,
        private readonly MfcApiClientInterface $mfcApiClient,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $session = $request->getSession();

        $email = $session->get('email');
        if (!$email) {
            throw new CustomUserMessageAuthenticationException('Произошла ошибка, попробуйте снова.');
        }

        $form = $this->formFactory->create(OtpType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            throw new CustomUserMessageAuthenticationException(implode(', ', $errors));
        }

        $otp = $form->get('otp')->getData();

        $expiredOtp = $this->emailOtpStorageService->hasEmailOtp($session->getId(), $email) === false;
        if ($expiredOtp) {
            throw new CustomUserMessageAuthenticationException('OTP-код истёк. Запросите новый.');
        }

        $trueOtp = $this->emailOtpStorageService->getEmailOtp($session->getId(), $email);
        if ($trueOtp !== $otp) {
            throw new CustomUserMessageAuthenticationException('Введен неверный OTP-код.');
        }

        try {
            $applicantStatuses = $this->mfcApiClient->getApplicantStatuses($email);
        } catch (MfcApiException $e) {
            throw new CustomUserMessageAuthenticationException($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('mfc-api-client-error', ['email' => $email, 'exception' => $e]);

            throw new CustomUserMessageAuthenticationException('Что-то пошло не так, попробуйте войти позднее.');
        }

        $roles = [];
        $documents = [];
        $userCode = null;
        foreach ($applicantStatuses as $applicantStatus) {
            $roles[] = match ($applicantStatus->status) {
                ApplicantStatusEnum::Student => 'ROLE_STUDENT',
                ApplicantStatusEnum::Employee => 'ROLE_EMPLOYEE',
                default => function () use ($email, $applicantStatus): void {
                    $this->logger->error('user with unknown status provided from api', ['email' => $email, 'status' => $applicantStatus->status->name]);
                    throw new CustomUserMessageAuthenticationException('Что-то пошло не так, попробуйте войти позднее.');
                },
            };

            if ($applicantStatus->document) {
                $documents[] = $applicantStatus->document;
            }

            if ($userCode === null) {
                $userCode = $applicantStatus->userCode;
            }
        }

        if ($userCode === null) {
            $this->logger->error('user without user code provided from api', ['email' => $email]);
            throw new CustomUserMessageAuthenticationException('Что-то пошло не так, попробуйте войти позднее.');
        }

        $roles = array_values(array_unique(array_filter($roles)));
        if ([] === $roles) {
            $this->logger->error('user without roles provided from api', ['email' => $email]);
            throw new CustomUserMessageAuthenticationException('Что-то пошло не так, попробуйте войти позднее.');
        }

        $documents = array_values(array_unique(array_filter($documents)));
        if (in_array('ROLE_STUDENT', $roles, true) && [] === $documents) {
            $this->logger->error('student without documents provided from api', ['email' => $email]);
            throw new CustomUserMessageAuthenticationException('Что-то пошло не так, попробуйте войти позднее.');
        }

        try {
            $this->userService->createOrUpdateUserForAuth($email, $userCode, $roles, $documents);
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->error('error while createOrUpdateUserForAuth', ['exception' => $e]);
            throw new CustomUserMessageAuthenticationException('Что-то пошло не так, попробуйте войти позднее.');
        }

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new SelfValidatingPassport(
            new UserBadge($email),
            [new RememberMeBadge()]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('dashboard'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
