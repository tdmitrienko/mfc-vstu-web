<?php

namespace App\Repository;

use App\Entity\MfcRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MfcRequest>
 */
class MfcRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MfcRequest::class);
    }

    /** @return MfcRequest[] */
    public function findRequestsByUser(User $user): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTemplateRequestByIdAndUser(int $id, User $user): MfcRequest|null
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.owner = :user')
            ->setParameter('user', $user)
            ->andWhere('m.id = :id')
            ->setParameter('id', $id)
            ->andWhere('m.state <> :state')
            ->setParameter('state', MfcRequest::STATE_DONE)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function removeRequest(MfcRequest $mfcRequest): void
    {
        $this->getEntityManager()->remove($mfcRequest);
        $this->getEntityManager()->flush();
    }
}
