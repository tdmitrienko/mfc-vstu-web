<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Table(name: 'users')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $email;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, MfcRequest>
     */
    #[ORM\OneToMany(targetEntity: MfcRequest::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $mfcRequests;

    #[ORM\Column(length: 32, unique: true)]
    private ?string $mfcCode = null;

    #[ORM\Column]
    private array $documents = [];

    public function __construct()
    {
        $this->mfcRequests = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantee every user at least has ROLE_USER
        if ($roles === []) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * @return Collection<int, MfcRequest>
     */
    public function getMfcRequests(): Collection
    {
        return $this->mfcRequests;
    }

    public function addMfcRequest(MfcRequest $mfcRequest): static
    {
        if (!$this->mfcRequests->contains($mfcRequest)) {
            $this->mfcRequests->add($mfcRequest);
            $mfcRequest->setOwner($this);
        }

        return $this;
    }

    public function removeMfcRequest(MfcRequest $mfcRequest): static
    {
        if ($this->mfcRequests->removeElement($mfcRequest)) {
            // set the owning side to null (unless already changed)
            if ($mfcRequest->getOwner() === $this) {
                $mfcRequest->setOwner(null);
            }
        }

        return $this;
    }

    public function getMfcCode(): ?string
    {
        return $this->mfcCode;
    }

    public function setMfcCode(string $mfcCode): static
    {
        $this->mfcCode = $mfcCode;

        return $this;
    }

    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function setDocuments(array $documents): static
    {
        $this->documents = $documents;

        return $this;
    }
}
