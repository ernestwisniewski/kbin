<?php

namespace App\Repository;

use App\Entity\EntryCommentReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntryCommentReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryCommentReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryCommentReport[]    findAll()
 * @method EntryCommentReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryCommentReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryCommentReport::class);
    }
}
