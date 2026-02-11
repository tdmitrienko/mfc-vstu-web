<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: [Request::METHOD_GET])]
    public function home(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login_email');
        }

        return $this->render('services/services.html.twig');
    }
}
