<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RemoteInstance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RemoteInstance>
 *
 * @method RemoteInstance|null find($id, $lockMode = null, $lockVersion = null)
 * @method RemoteInstance|null findOneBy(array $criteria, array $orderBy = null)
 * @method RemoteInstance[]    findAll()
 * @method RemoteInstance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RemoteInstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RemoteInstance::class);
    }

    //    /**
    //     * @return RemoteInstance[] Returns an array of RemoteInstance objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RemoteInstance
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
