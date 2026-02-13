<?php

namespace App\Service;

use App\Entity\MfcRequest;
use App\Entity\MfcRequestFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\ByteString;

final class MfcFileStorage
{
    public function __construct(
        private string $uploadDir,
    ) {}

    public function storeUploadedFile(MfcRequest $req, UploadedFile $file): MfcRequestFile
    {
        $subDir = (string) $req->getId();
        $targetDir = rtrim($this->uploadDir, '/').'/'.$subDir;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $ext = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $name = ByteString::fromRandom(24)->toString().'.'.$ext;

        $file->move($targetDir, $name);

        $entity = new MfcRequestFile();
        $entity->setPath($subDir.'/'.$name);
        $entity->setOriginalName($file->getClientOriginalName());
        $entity->setMimeType($file->getClientMimeType());
        $entity->setSize($file->getSize());
        $req->addFile($entity);

        return $entity;
    }

    public function deletePhysicalFile(MfcRequestFile $file): void
    {
        $fullPath = rtrim($this->uploadDir, '/').'/'.$file->getPath();
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
