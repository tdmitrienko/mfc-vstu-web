<?php

namespace App\Security;

use App\Form\OtpType;
use App\Service\Cache\EmailOtpStorageService;
use App\Service\UserService;
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
        private UrlGeneratorInterface $urlGenerator,
        private EmailOtpStorageService $emailOtpStorageService,
        private FormFactoryInterface $formFactory,
        private UserService $userService,
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

        $this->userService->findOrCreateUser($email);

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

        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
