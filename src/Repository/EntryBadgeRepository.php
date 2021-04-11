<?php

namespace App\Repository;

use App\Entity\EntryBadge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntryBadge|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryBadge|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryBadge[]    findAll()
 * @method EntryBadge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryBadgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryBadge::class);
    }
}
