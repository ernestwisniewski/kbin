<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\UserFollow;

/**
 * @method UserFollow|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFollow|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFollow[]    findAll()
 * @method UserFollow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFollow::class);
    }
}
