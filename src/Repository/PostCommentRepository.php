<?php

// SPDX-FileCopyrightText: Copyright (c) 2016-2017 Emma <emma1312@protonmail.ch>
//
// SPDX-License-Identifier: Zlib

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\PostComment;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use App\PageView\PostCommentPageView;
use App\Repository\Contract\TagRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method PostComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostComment[]    findAll()
 * @method PostComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostCommentRepository extends ServiceEntityRepository implements TagRepositoryInterface
{
    public const PER_PAGE = 15;

    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, PostComment::class);

        $this->security = $security;
    }

    public function findByCriteria(PostCommentPageView $criteria)
    {
        //        return $this->createQueryBuilder('pc')
        //            ->orderBy('pc.createdAt', 'DESC')
        //            ->setMaxResults(10)
        //            ->getQuery()
        //            ->getResult();
        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $this->getCommentQueryBuilder($criteria),
                false
            )
        );

        try {
            $pagerfanta->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
            $pagerfanta->setCurrentPage($criteria->page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    private function getCommentQueryBuilder(Criteria $criteria): QueryBuilder
    {
        $user = $this->security->getUser();

        $qb = $this->createQueryBuilder('c')
            ->select('c', 'u')
            ->join('c.user', 'u')
            ->andWhere('c.visibility IN (:visibility)');

        if ($user && VisibilityInterface::VISIBILITY_VISIBLE === $criteria->visibility) {
            $qb->orWhere(
                'c.user IN (SELECT IDENTITY(cuf.following) FROM '.UserFollow::class.' cuf WHERE cuf.follower = :cUser AND c.visibility = :cVisibility)'
            )
                ->setParameter('cUser', $user)
                ->setParameter('cVisibility', VisibilityInterface::VISIBILITY_PRIVATE);
        }

        $qb->setParameter(
            'visibility',
            [
                VisibilityInterface::VISIBILITY_SOFT_DELETED,
                VisibilityInterface::VISIBILITY_VISIBLE,
                VisibilityInterface::VISIBILITY_TRASHED,
            ]
        );

        $this->addTimeClause($qb, $criteria);
        $this->filter($qb, $criteria);

        return $qb;
    }

    private function addTimeClause(QueryBuilder $qb, Criteria $criteria): void
    {
        if (Criteria::TIME_ALL !== $criteria->time) {
            $since = $criteria->getSince();

            $qb->andWhere('c.createdAt > :time')
                ->setParameter('time', $since, Types::DATETIMETZ_IMMUTABLE);
        }
    }

    private function filter(QueryBuilder $qb, Criteria $criteria)
    {
        if ($criteria->post) {
            $qb->andWhere('c.post = :post')
                ->setParameter('post', $criteria->post);
        }

        if ($criteria->magazine) {
            $qb->join('c.post', 'p', Join::WITH, 'p.magazine = :magazine');
            $qb->setParameter('magazine', $criteria->magazine);
        }

        if ($criteria->languages) {
            $qb->andWhere('c.lang IN (:languages)')
                ->setParameter('languages', $criteria->languages, ArrayParameterType::STRING);
        }

        if ($criteria->user) {
            $qb->andWhere('c.user = :user')
                ->setParameter('user', $criteria->user);
        }

        $user = $this->security->getUser();
        if ($user && !$criteria->moderated) {
            $qb->andWhere(
                'c.user NOT IN (SELECT IDENTITY(ub.blocked) FROM '.UserBlock::class.' ub WHERE ub.blocker = :blocker)'
            );

            $qb->setParameter('blocker', $user);
        }

        if ($criteria->onlyParents) {
            $qb->andWhere('c.parent IS NULL');
        }

        switch ($criteria->sortOption) {
            case Criteria::SORT_HOT:
            case Criteria::SORT_TOP:
                $qb->orderBy('c.upVotes', 'DESC');
                break;
            case Criteria::SORT_ACTIVE:
                $qb->orderBy('c.lastActive', 'DESC');
                break;
            case Criteria::SORT_NEW:
                $qb->orderBy('c.createdAt', 'DESC');
                break;
            case Criteria::SORT_OLD:
                $qb->orderBy('c.createdAt', 'ASC');
                break;
            default:
                $qb->addOrderBy('c.lastActive', 'DESC');
        }

        $qb->addOrderBy('c.createdAt', 'DESC');
        $qb->addOrderBy('c.id', 'DESC');
    }

    public function hydrate(PostComment ...$comment): void
    {
        $this->_em->createQueryBuilder()
            ->select('PARTIAL c.{id}')
            ->addSelect('u')
            ->addSelect('m')
            ->addSelect('i')
            ->from(PostComment::class, 'c')
            ->join('c.user', 'u')
            ->join('c.magazine', 'm')
            ->leftJoin('c.image', 'i')
            ->where('c IN (?1)')
            ->setParameter(1, $comment)
            ->getQuery()
            ->getResult();

        if ($this->security->getUser()) {
            $this->_em->createQueryBuilder()
                ->select('PARTIAL c.{id}')
                ->addSelect('cv')
                ->addSelect('cf')
                ->from(PostComment::class, 'c')
                ->leftJoin('c.votes', 'cv')
                ->leftJoin('c.favourites', 'cf')
                ->where('c IN (?1)')
                ->setParameter(1, $comment)
                ->getQuery()
                ->getResult();
        }
    }

    public function findToDelete(User $user, int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.visibility != :visibility')
            ->andWhere('c.user = :user')
            ->setParameters(['visibility' => PostComment::VISIBILITY_SOFT_DELETED, 'user' => $user])
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findFederated()
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.apId IS NOT NULL')
            ->andWhere('c.visibility = :visibility')
            ->orderBy('c.createdAt', 'DESC')
            ->setParameter('visibility', VisibilityInterface::VISIBILITY_VISIBLE)
            ->getQuery()
            ->getResult();
    }

    public function findWithTags(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.tags IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
