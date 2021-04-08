<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\MessageThread;
use Pagerfanta\Pagerfanta;
use App\Entity\User;

/**
 * @method MessageThread|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageThread|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageThread|null findOneByName(string $name)
 * @method MessageThread[]    findAll()
 * @method MessageThread[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageThread::class);
    }

    public function findUserMessages(?User $user, $page)
    {
        $qb = $this->createQueryBuilder('mt')
            ->where(':user MEMBER OF mt.participants')
            ->orderBy('mt.updatedAt', 'DESC')
            ->setParameter(':user', $user);

        $pager = new Pagerfanta(new QueryAdapter($qb));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
