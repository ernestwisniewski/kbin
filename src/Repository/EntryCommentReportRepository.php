<?php

namespace App\Repository;

use App\Entity\EntryComment;
use App\Entity\EntryCommentReport;
use App\Entity\Report;
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

    public function findBySubject(EntryComment $comment)
    {
        return $this->createQueryBuilder('r')
            ->where('r.entryComment = :comment')
            ->setParameter('comment', $comment)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPendingBySubject(EntryComment $comment)
    {
        return $this->createQueryBuilder('r')
            ->where('r.entryComment = :comment')
            ->setParameter('comment', $comment)
            ->andWhere('r.status = :status')
            ->setParameter('status', Report::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
