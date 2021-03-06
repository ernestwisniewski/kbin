<?php

namespace App\Repository;

use App\Entity\PostReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostReport[]    findAll()
 * @method PostReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostReport::class);
    }
}
