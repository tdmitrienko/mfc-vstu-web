<?php

namespace App\Entity;

use App\Repository\ApplicationTypeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationTypeRepository::class)]
class ApplicationType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    private ?string $name = null;

    #[ORM\Column(length: 128, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column]
    private bool $documentRequired = true;

    #[ORM\Column]
    private bool $filesRequired = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function isDocumentRequired(): bool
    {
        return $this->documentRequired;
    }

    public function setDocumentRequired(bool $documentRequired): static
    {
        $this->documentRequired = $documentRequired;

        return $this;
    }

    public function isFilesRequired(): bool
    {
        return $this->filesRequired;
    }

    public function setFilesRequired(bool $filesRequired): static
    {
        $this->filesRequired = $filesRequired;

        return $this;
    }
}
