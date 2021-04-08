<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\PostCommentReport;
use App\Entity\PostComment;
use App\Entity\Report;

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

    public function findBySubject(PostComment $comment)
    {
        return $this->createQueryBuilder('r')
            ->where('r.postComment = :comment')
            ->setParameter('comment', $comment)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPendingBySubject(PostComment $comment)
    {
        return $this->createQueryBuilder('r')
            ->where('r.postComment = :comment')
            ->setParameter('comment', $comment)
            ->andWhere('r.status = :status')
            ->setParameter('status', Report::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
