<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MessageThread;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method MessageThread|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageThread|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageThread|null findOneByName(string $name)
 * @method MessageThread[]    findAll()
 * @method MessageThread[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageThreadRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageThread::class);
    }

    public function findUserMessages(?User $user, int $page, int $perPage = self::PER_PAGE)
    {
        $qb = $this->createQueryBuilder('mt')
            ->where(':user MEMBER OF mt.participants')
            ->orderBy('mt.updatedAt', 'DESC')
            ->setParameter(':user', $user);

        $pager = new Pagerfanta(new QueryAdapter($qb));
        try {
            $pager->setMaxPerPage($perPage);
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pager;
    }
}
