<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
// SPDX-FileCopyrightText: Copyright (c) 2016-2017 Emma <emma1312@protonmail.ch>
//
// SPDX-License-Identifier: AGPL-3.0-only
// SPDX-License-Identifier: Zlib

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\DomainBlock;
use App\Entity\DomainSubscription;
use App\Entity\EntryComment;
use App\Entity\EntryCommentFavourite;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\Moderator;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use App\Kbin\Pagination\AdapterFactory;
use App\Repository\Contract\TagRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method EntryComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryComment[]    findAll()
 * @method EntryComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryCommentRepository extends ServiceEntityRepository implements TagRepositoryInterface
{
    public const SORT_DEFAULT = 'active';
    public const PER_PAGE = 15;

    public function __construct(
        ManagerRegistry $registry,
        private readonly Security $security,
        private readonly AdapterFactory $adapterFactory,
    ) {
        parent::__construct($registry, EntryComment::class);
    }

    public function findByCriteria(Criteria $criteria): PagerfantaInterface
    {
        $pagerfanta = new Pagerfanta($this->adapterFactory->create($this->getEntryQueryBuilder($criteria)));

        try {
            $pagerfanta->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
            $pagerfanta->setCurrentPage($criteria->page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    private function getEntryQueryBuilder(Criteria $criteria): QueryBuilder
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

    private function filter(QueryBuilder $qb, Criteria $criteria): QueryBuilder
    {
        $user = $this->security->getUser();

        if (Criteria::AP_LOCAL === $criteria->federation) {
            $qb->andWhere('c.apId IS NULL');
        }

        if ($criteria->entry) {
            $qb->andWhere('c.entry = :entry')
                ->setParameter('entry', $criteria->entry);
        }

        if ($criteria->magazine) {
            $qb->join('c.entry', 'e', Join::WITH, 'e.magazine = :magazine')
                ->setParameter('magazine', $criteria->magazine)
                ->andWhere('e.visibility = :visible')
                ->setParameter('visible', VisibilityInterface::VISIBILITY_VISIBLE);
        } else {
            $qb->join('c.entry', 'e')
                ->andWhere('e.visibility = :visible')
                ->setParameter('visible', VisibilityInterface::VISIBILITY_VISIBLE);
        }

        if ($criteria->user) {
            $qb->andWhere('c.user = :criteria_user')
                ->setParameter('criteria_user', $criteria->user);
        }

        $qb->join('c.entry', 'ce');

        if ($criteria->domain) {
            $qb->andWhere('ced.name = :domain')
                ->join('ce.domain', 'ced')
                ->setParameter('domain', $criteria->domain);
        }

        if ($criteria->languages) {
            $qb->andWhere('c.lang IN (:languages)')
                ->setParameter('languages', $criteria->languages, ArrayParameterType::STRING);
        }

        if ($criteria->tag) {
            $qb->andWhere("JSONB_CONTAINS(c.tags, '\"".$criteria->tag."\"') = true");
        }

        if ($criteria->subscribed) {
            $subQuery = 'e.user = :user';

            if ($criteria->showSubscribedUsers) {
                $subQuery .= ' OR c.user IN (SELECT IDENTITY(uf.following) FROM '.UserFollow::class.' uf WHERE uf.follower = :user)';
            }

            if ($criteria->showSubscribedMagazines) {
                $subQuery .= ' OR c.magazine IN (SELECT IDENTITY(ms.magazine) FROM '.MagazineSubscription::class.' ms WHERE ms.user = :user)';
            }

            if ($criteria->showSubscribedDomains) {
                $subQuery .= ' OR ce.domain IN (SELECT IDENTITY(ds.domain) FROM '.DomainSubscription::class.' ds WHERE ds.user = :user)';
            }

            $qb->andWhere($subQuery)
                ->setParameter('user', $this->security->getUser());
        }

        if ($criteria->moderated) {
            $qb->andWhere(
                'c.magazine IN (SELECT IDENTITY(cm.magazine) FROM '.Moderator::class.' cm WHERE cm.user = :user)'
            );
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($criteria->favourite) {
            $qb->andWhere(
                'c.id IN (SELECT IDENTITY(cf.entryComment) FROM '.EntryCommentFavourite::class.' cf WHERE cf.user = :user)'
            );
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($user && (!$criteria->magazine || !$criteria->magazine->userIsModerator($user)) && !$criteria->moderated) {
            $qb->andWhere(
                'c.user NOT IN (SELECT IDENTITY(ub.blocked) FROM '.UserBlock::class.' ub WHERE ub.blocker = :blocker)'
            );

            $qb->andWhere(
                'ce.user NOT IN (SELECT IDENTITY(ubc.blocked) FROM '.UserBlock::class.' ubc WHERE ubc.blocker = :blocker)'
            );

            $qb->andWhere(
                'c.magazine NOT IN (SELECT IDENTITY(mb.magazine) FROM '.MagazineBlock::class.' mb WHERE mb.user = :blocker)'
            );

            if (!$criteria->domain) {
                $qb->andWhere(
                    'ce.domain IS null OR ce.domain NOT IN (SELECT IDENTITY(db.domain) FROM '.DomainBlock::class.' db WHERE db.user = :blocker)'
                );
            }

            $qb->setParameter('blocker', $user);
        }

        if ($criteria->onlyParents) {
            $qb->andWhere('c.parent IS NULL');
        }

        if (!$user || $user->hideAdult) {
            $qb->join('e.magazine', 'm')
                ->andWhere('m.isAdult = :isAdult')
                ->andWhere('e.isAdult = :isAdult')
                ->setParameter('isAdult', false);
        }

        switch ($criteria->sortOption) {
            case Criteria::SORT_HOT:
                $qb->orderBy('c.upVotes', 'DESC');
                break;
            case Criteria::SORT_TOP:
                $qb->orderBy('c.favouriteCount - c.downVotes', 'DESC');
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

        return $qb;
    }

    public function hydrateChildren(EntryComment ...$comments): void
    {
        $children = $this->createQueryBuilder('c')
            ->andWhere('c.root IN (:ids)')
            ->setParameter('ids', $comments)
            ->getQuery()->getResult();

        $this->hydrate(...$children);
    }

    public function hydrate(EntryComment ...$comments): void
    {
        $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id}')
            ->addSelect('u')
            ->addSelect('e')
            ->addSelect('v')
            ->addSelect('em')
            ->addSelect('f')
            ->join('c.user', 'u')
            ->join('c.entry', 'e')
            ->join('c.votes', 'v')
            ->leftJoin('c.favourites', 'f')
            ->join('e.magazine', 'em')
            ->where('c IN (?1)')
            ->setParameter(1, $comments)
            ->getQuery()
            ->execute();

        $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id}')
            ->addSelect('cc')
            ->addSelect('ccu')
            ->addSelect('ccua')
            ->addSelect('ccv')
            ->addSelect('ccf')
            ->leftJoin('c.children', 'cc')
            ->join('cc.user', 'ccu')
            ->leftJoin('ccu.avatar', 'ccua')
            ->leftJoin('cc.votes', 'ccv')
            ->leftJoin('cc.favourites', 'ccf')
            ->where('c IN (?1)')
            ->setParameter(1, $comments)
            ->getQuery()
            ->execute();
    }

    public function hydrateParents(EntryComment ...$comments): void
    {
        $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id}')
            ->addSelect('cp')
            ->addSelect('cpu')
            ->addSelect('cpe')
            ->leftJoin('c.parent', 'cp')
            ->leftJoin('cp.user', 'cpu')
            ->leftJoin('cp.entry', 'cpe')
            ->where('c IN (?1)')
            ->setParameter(1, $comments)
            ->getQuery()
            ->execute();
    }

    public function findToDelete(User $user, int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.visibility != :visibility')
            ->andWhere('c.user = :user')
            ->setParameters(['visibility' => VisibilityInterface::VISIBILITY_SOFT_DELETED, 'user' => $user])
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($limit)
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
