<?php

namespace App\Service;

use App\Entity\MfcRequest;
use App\Entity\MfcRequestFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\ByteString;

final class MfcFileStorage
{
    public function __construct(
        private readonly string $uploadDir,
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

        $size = $file->getSize();
        $file->move($targetDir, $name);

        $entity = new MfcRequestFile();
        $entity->setPath($subDir.'/'.$name);
        $entity->setOriginalName($file->getClientOriginalName());
        $entity->setMimeType($file->getClientMimeType());
        $entity->setSize($size);
        $req->addFile($entity);

        return $entity;
    }

    public function deletePhysicalFile(MfcRequestFile $file): void
    {
        $fullPath = $this->getFullPath($file->getPath());
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    public function deletePhysicalFileByRelativePath(string $relativePath): void
    {
        $fullPath = $this->getFullPath($relativePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    public function getSplFileInfo(MfcRequestFile $file): ?\SplFileInfo
    {
        $fullPath = $this->getFullPath($file->getPath());

        return is_file($fullPath) ? new \SplFileInfo($fullPath) : null;
    }

    private function getFullPath(string $relativePath): string
    {
        return rtrim($this->uploadDir, '/').'/'.$relativePath;
    }
}
