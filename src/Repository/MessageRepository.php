<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Message;
use App\Kbin\Message\MessageThreadPageView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message|null findOneByName(string $name)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findByCriteria(MessageThreadPageView|Criteria $criteria): PagerfantaInterface
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.thread = :m_thread_id')
            ->setParameter('m_thread_id', $criteria->thread->getId());

        switch ($criteria->sortOption) {
            case Criteria::SORT_OLD:
                $qb->orderBy('m.createdAt', 'ASC');
                break;
            default:
                $qb->orderBy('m.createdAt', 'DESC');
        }

        $messages = new Pagerfanta(
            new QueryAdapter(
                $qb,
                false
            )
        );

        try {
            $messages->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
            $messages->setCurrentPage($criteria->page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $messages;
    }
}
