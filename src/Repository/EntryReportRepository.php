<?php

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\EntryReport;
use App\Entity\Report;
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

    public function findBySubject(Entry $entry)
    {
        return $this->createQueryBuilder('r')
            ->where('r.entry = :entry')
            ->setParameter('entry', $entry)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPendingBySubject(Entry $entry)
    {
        return $this->createQueryBuilder('r')
            ->where('r.entry = :entry')
            ->setParameter('entry', $entry)
            ->andWhere('r.status = :status')
            ->setParameter('status', Report::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
