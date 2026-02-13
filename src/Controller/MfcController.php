<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mfc')]
class MfcController extends AbstractController
{
    #[Route('/', name: 'mfc')]
    public function mfc(): Response
    {
        return $this->render('mfc/index.html.twig');
    }

    #[Route('/upload', name: 'mfc_upload')]
    public function mfcUpload(Request $request): Response
    {
        // Если нажали кнопку "Далее"
        if ($request->isMethod('POST')) {

            // Здесь позже будет логика загрузки файлов

            return $this->redirectToRoute('mfc_success');
        }

        return $this->render('mfc/upload.html.twig');
    }

    #[Route('/success', name: 'mfc_success')]
    public function mfcSuccess(): Response
    {
        return $this->render('mfc/success.html.twig');
    }

    #[Route('/status', name: 'mfc_status')]
    public function mfcStatus(): Response
    {
        return $this->render('mfc/status.html.twig');
    }
}
