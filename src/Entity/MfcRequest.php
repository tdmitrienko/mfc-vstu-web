<?php

namespace App\Entity;

use App\Repository\MfcRequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MfcRequestRepository::class)]
class MfcRequest
{
    public const STATE_STEP1 = 'step1';
    public const STATE_STEP2 = 'step2';
    public const STATE_DONE  = 'done';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 16)]
    private string $state = self::STATE_STEP1;

    #[ORM\ManyToOne(inversedBy: 'mfcRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /** @var Collection<int, MfcRequestFile> */
    #[ORM\OneToMany(targetEntity: MfcRequestFile::class, mappedBy: 'request', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\ManyToOne]
    private ?ApplicationType $applicationType = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $documentNumber = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getDocumentNumber(): ?string
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber(?string $documentNumber): static
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }

    public function getApplicationType(): ?ApplicationType
    {
        return $this->applicationType;
    }

    public function setApplicationType(?ApplicationType $applicationType): static
    {
        $this->applicationType = $applicationType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /** @return Collection<int, MfcRequestFile> */
    public function getFiles(): Collection { return $this->files; }

    public function addFile(MfcRequestFile $file): void
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setRequest($this);
        }
    }

    public function removeFile(MfcRequestFile $file): void
    {
        if ($this->files->removeElement($file)) {
            $file->setRequest(null);
        }
    }
}
