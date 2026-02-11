<?php

namespace App\Controller;

use App\Form\EmailType;
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
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(EmailType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $session->set('email', $email);

            //1) отправляем OTP, сохраняем его
            //2) пишем OTP + email в кэш на 5 минут

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
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
