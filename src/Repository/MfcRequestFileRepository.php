<?php

namespace App\Repository;

use App\Entity\MfcRequestFile;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MfcRequestFile>
 */
class MfcRequestFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MfcRequestFile::class);
    }

    public function findFileByIdAndUser(int $id, User $user): MfcRequestFile|null
    {
        return $this->createQueryBuilder('f')
            ->innerJoin('f.request', 'r')
            ->andWhere('f.id = :id')
            ->setParameter('id', $id)
            ->andWhere('r.owner = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
