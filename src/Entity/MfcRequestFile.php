<?php

namespace App\Entity;

use App\Repository\MfcRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MfcRequestRepository::class)]
class MfcRequestFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MfcRequest $request = null;

    #[ORM\Column(length: 255)]
    private string $path;

    #[ORM\Column(length: 255)]
    private string $originalName;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(nullable: true)]
    private ?int $size = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getRequest(): ?MfcRequest { return $this->request; }
    public function setRequest(?MfcRequest $r): void { $this->request = $r; }

    public function getPath(): string { return $this->path; }
    public function setPath(string $path): void { $this->path = $path; }

    public function getOriginalName(): string { return $this->originalName; }
    public function setOriginalName(string $n): void { $this->originalName = $n; }

    public function getMimeType(): ?string { return $this->mimeType; }
    public function setMimeType(?string $m): void { $this->mimeType = $m; }

    public function getSize(): ?int { return $this->size; }
    public function setSize(?int $s): void { $this->size = $s; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}
