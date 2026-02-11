<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ServicesController extends AbstractController
{
    #[Route('/services', name: 'app_services')]
    public function index(): Response
    {
        return $this->render('services/services.html.twig');
    }

    #[Route('/mfc', name: 'app_mfc')]
    public function mfc(): Response
    {
        return $this->render('services/mfc_step.html.twig');
    }

    #[Route('/mfc/upload', name: 'app_mfc_upload')]
    public function mfcUpload(Request $request): Response
    {
        // Если нажали кнопку "Далее"
        if ($request->isMethod('POST')) {

            // Здесь позже будет логика загрузки файлов

            return $this->redirectToRoute('app_mfc_success');
        }

        return $this->render('services/mfc_upload.html.twig');
    }

    #[Route('/mfc/success', name: 'app_mfc_success')]
    public function mfcSuccess(): Response
    {
        return $this->render('services/mfc_success.html.twig');
    }

    #[Route('/mfc/status', name: 'app_mfc_status')]
    public function mfcStatus(): Response
    {
        return $this->render('services/mfc_status.html.twig');
    }
}
