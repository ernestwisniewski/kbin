<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostReport;
use App\Entity\Report;
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

    public function findBySubject(Post $post)
    {
        return $this->createQueryBuilder('r')
            ->where('r.post = :post')
            ->setParameter('post', $post)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPendingBySubject(Post $post)
    {
        return $this->createQueryBuilder('r')
            ->where('r.post = :post')
            ->setParameter('post', $post)
            ->andWhere('r.status = :status')
            ->setParameter('status', Report::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
