<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MfcRequestFileRepository;
use App\Service\MfcFileStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/mfc/file')]
class MfcRequestFileController extends AbstractController
{
    #[Route('/{id}', name: 'mfc_file_show', methods: ['GET'])]
    public function show(
        MfcRequestFileRepository $mfcRequestFileRepository,
        int $id,
        MfcFileStorage $storage,
    ): BinaryFileResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $file = $mfcRequestFileRepository->findFileByIdAndUser($id, $user);
        if ($file === null) {
            throw $this->createNotFoundException('Page not found');
        }

        $fileInfo = $storage->getSplFileInfo($file);

        return $this->file(
            $fileInfo,
            $file->getOriginalName(),
            ResponseHeaderBag::DISPOSITION_INLINE // inline = откроет в браузере если возможно (pdf/img), иначе скачает
        );
    }

    #[Route('/{id}/download', name: 'mfc_file_download', methods: ['GET'])]
    public function download(
        MfcRequestFileRepository $mfcRequestFileRepository,
        int $id,
        MfcFileStorage $storage,
    ): BinaryFileResponse {
        /** @var User $user */
        $user = $this->getUser();

        $file = $mfcRequestFileRepository->findFileByIdAndUser($id, $user);
        if ($file === null) {
            throw $this->createNotFoundException('Page not found');
        }

        $fileInfo = $storage->getSplFileInfo($file);

        return $this->file(
            $fileInfo,
            $file->getOriginalName(),
            ResponseHeaderBag::DISPOSITION_ATTACHMENT // скачать файл сразу
        );
    }
}
