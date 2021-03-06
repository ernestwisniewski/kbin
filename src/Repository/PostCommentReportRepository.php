<?php

namespace App\Repository;

use App\Entity\PostCommentReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostCommentReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostCommentReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostCommentReport[]    findAll()
 * @method PostCommentReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostCommentReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostCommentReport::class);
    }
}
