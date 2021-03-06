<?php

namespace App\Repository;

use App\Entity\EntryReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntryReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryReport[]    findAll()
 * @method EntryReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryReport::class);
    }
}
