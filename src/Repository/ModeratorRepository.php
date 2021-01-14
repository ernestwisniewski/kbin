<?php

namespace App\Repository;

use App\Entity\Moderator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Moderator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Moderator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Moderator[]    findAll()
 * @method Moderator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModeratorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Moderator::class);
    }

    // /**
    //  * @return Moderator[] Returns an array of Moderator objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Moderator
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
