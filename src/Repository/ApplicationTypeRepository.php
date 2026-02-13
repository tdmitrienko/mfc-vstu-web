<?php

namespace App\Repository;

use App\Entity\ApplicationType;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApplicationType>
 */
class ApplicationTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationType::class);
    }

    /**
     * @return ApplicationType[]
     */
    public function findSuitableByUser(User $user): array
    {
        $applicationTypes = $this->findAll();

        $suitable = [];
        foreach ($applicationTypes as $applicationType) {
            $hasAny = !empty(array_intersect($applicationType->getRoles(), $user->getRoles()));
            if ($hasAny) {
                $suitable[] = $applicationType;
            }
        }

        return $suitable;
    }
}
