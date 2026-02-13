<?php

namespace App\Controller;

use App\Form\EmailType;
use App\Form\OtpType;
use App\Service\Cache\EmailOtpStorageService;
use App\Service\Communication\EmailSender;
use App\Service\OtpGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login_email', methods: ['GET', 'POST'])]
    public function loginEmailStep(
        AuthenticationUtils $authenticationUtils,
        Request $request,
        Session $session,
        OtpGenerator $otpGenerator,
        EmailOtpStorageService $emailOtpStorageService,
        EmailSender $emailSender,
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }

        $form = $this->createForm(EmailType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $session->set('email', $email);

            $issetOtp = $emailOtpStorageService->hasEmailOtp($session->getId(), $email);
            if ($issetOtp) {
                return $this->redirectToRoute('app_login_otp');
            }

            $ttlMin = 5;
            $otp = $otpGenerator->generateNumeric();
            $emailOtpStorageService->setEmailOtp($session->getId(), $email, $otp, $ttlMin * 60);

            $emailSender->sendOtpLoginEmail($email, $otp, $ttlMin);

            return $this->redirectToRoute('app_login_otp');
        }

        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/email_step.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $errors === [] ? null : implode(', ', $errors),
            'last_auth_error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route(path: '/login/otp', name: 'app_login_otp', methods: ['GET', 'POST'])]
    public function loginOtpStep(
        AuthenticationUtils $authenticationUtils,
        EmailOtpStorageService $emailOtpStorageService,
        Session $session,
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }

        $email = $session->get('email');
        $expiredOtp = $emailOtpStorageService->hasEmailOtp($session->getId(), $email) === false;
        if (!$email || $expiredOtp) {
            return $this->redirectToRoute('app_login_email');
        }

        return $this->render('security/otp.html.twig', [
            'form' => $this->createForm(OtpType::class)->createView(),
            'last_auth_error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
