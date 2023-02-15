<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\DomainBlock;
use App\Entity\DomainSubscription;
use App\Entity\Entry;
use App\Entity\EntryFavourite;
use App\Entity\Magazine;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\Moderator;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use App\PageView\EntryPageView;
use App\Repository\Contract\TagRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;

/**
 * @method Entry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entry[]    findAll()
 * @method Entry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryRepository extends ServiceEntityRepository implements TagRepositoryInterface
{
    public const SORT_DEFAULT = 'hot';
    public const TIME_DEFAULT = Criteria::TIME_ALL;
    public const PER_PAGE = 25;

    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Entry::class);

        $this->security = $security;
    }

    public function findByCriteria(EntryPageView|Criteria $criteria): PagerfantaInterface
    {
        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $this->getEntryQueryBuilder($criteria)
            )
        );

        try {
            $pagerfanta->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
            $pagerfanta->setCurrentPage($criteria->page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $this->hydrate(...$pagerfanta->getCurrentPageResults());

        return $pagerfanta;
    }

    private function getEntryQueryBuilder(EntryPageView $criteria): QueryBuilder
    {
        $user = $this->security->getUser();

        $qb = $this->createQueryBuilder('e')
            ->where('e.visibility = :e_visibility')
            ->join('e.magazine', 'm')
            ->andWhere('m.visibility = :m_visibility');

        if ($user && VisibilityInterface::VISIBILITY_VISIBLE === $criteria->visibility) {
            $qb->orWhere(
                'e.user IN (SELECT IDENTITY(puf.following) FROM '.UserFollow::class.' puf WHERE puf.follower = :pUser AND e.visibility = :pVisibility)'
            )
                ->setParameter('pUser', $user)
                ->setParameter('pVisibility', VisibilityInterface::VISIBILITY_PRIVATE);
        }

        $qb->setParameter('e_visibility', $criteria->visibility)
            ->setParameter('m_visibility', VisibilityInterface::VISIBILITY_VISIBLE);

        $this->addTimeClause($qb, $criteria);
        $this->addStickyClause($qb, $criteria);
        $this->filter($qb, $criteria);

        return $qb;
    }

    private function addTimeClause(QueryBuilder $qb, EntryPageView $criteria)
    {
        if (Criteria::TIME_ALL !== $criteria->time) {
            $since = $criteria->getSince();

            $qb->andWhere('e.createdAt > :time')
                ->setParameter('time', $since, Types::DATETIMETZ_IMMUTABLE);
        }
    }

    private function addStickyClause(QueryBuilder $qb, EntryPageView $criteria)
    {
        if ($criteria->stickiesFirst) {
            if (1 === $criteria->page) {
                $qb->addOrderBy('e.sticky', 'DESC');
            } else {
                $qb->andWhere($qb->expr()->eq('e.sticky', 'false'));
            }
        }
    }

    private function filter(QueryBuilder $qb, EntryPageView $criteria): QueryBuilder
    {
        $user = $this->security->getUser();

        if ($criteria->magazine) {
            $qb->andWhere('e.magazine = :magazine')
                ->setParameter('magazine', $criteria->magazine);
        }

        if ($criteria->user) {
            $qb->andWhere('e.user = :user')
                ->setParameter('user', $criteria->user);
        }

        if ($criteria->type) {
            $qb->andWhere('e.type = :type')
                ->setParameter('type', $criteria->type);
        }

        if ($criteria->tag) {
            $qb->andWhere("JSONB_CONTAINS(e.tags, '\"".$criteria->tag."\"') = true");
        }

        if ($criteria->domain) {
            $qb->andWhere('ed.name = :domain')
                ->join('e.domain', 'ed')
                ->setParameter('domain', $criteria->domain);
        }

        if ($criteria->subscribed) {
            $qb->andWhere(
                'e.magazine IN (SELECT IDENTITY(ms.magazine) FROM '.MagazineSubscription::class.' ms WHERE ms.user = :user) 
                OR 
                e.user IN (SELECT IDENTITY(uf.following) FROM '.UserFollow::class.' uf WHERE uf.follower = :user)
                OR 
                e.domain IN (SELECT IDENTITY(ds.domain) FROM '.DomainSubscription::class.' ds WHERE ds.user = :user)
                OR
                e.user = :user'
            )
                ->setParameter('user', $this->security->getUser());
        }

        if ($criteria->moderated) {
            $qb->andWhere(
                'e.magazine IN (SELECT IDENTITY(mm.magazine) FROM '.Moderator::class.' mm WHERE mm.user = :user)'
            );
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($criteria->favourite) {
            $qb->andWhere(
                'e.id IN (SELECT IDENTITY(mf.entry) FROM '.EntryFavourite::class.' mf WHERE mf.user = :user)'
            );
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($user && (!$criteria->magazine || !$criteria->magazine->userIsModerator($user)) && !$criteria->moderated) {
            $qb->andWhere(
                'e.user NOT IN (SELECT IDENTITY(ub.blocked) FROM '.UserBlock::class.' ub WHERE ub.blocker = :blocker)'
            );

            $qb->andWhere(
                'e.magazine NOT IN (SELECT IDENTITY(mb.magazine) FROM '.MagazineBlock::class.' mb WHERE mb.user = :blocker)'
            );

            if (!$criteria->domain) {
                $qb->andWhere(
                    'e.domain NOT IN (SELECT IDENTITY(db.domain) FROM '.DomainBlock::class.' db WHERE db.user = :blocker)'
                );
            }

            $qb->setParameter('blocker', $user);
        }

        if (!$user || $user->hideAdult) {
            $qb->andWhere('m.isAdult = :isAdult')
                ->setParameter('isAdult', false);
        }

        switch ($criteria->sortOption) {
            case Criteria::SORT_TOP:
                $qb->addOrderBy('e.score', 'DESC');
                break;
            case Criteria::SORT_HOT:
                $qb->addOrderBy('e.ranking', 'DESC');
                break;
            case Criteria::SORT_COMMENTED:
                $qb->addOrderBy('e.commentCount', 'DESC');
                break;
            case Criteria::SORT_ACTIVE:
                $qb->addOrderBy('e.lastActive', 'DESC');
                break;
            default:
        }

        $qb->addOrderBy('e.createdAt', 'DESC');
        $qb->addOrderBy('e.id', 'DESC');

        return $qb;
    }

    public function hydrate(Entry ...$entries): void
    {
        $this->_em->createQueryBuilder()
            ->select('PARTIAL e.{id}')
            ->addSelect('u')
            ->addSelect('ua')
            ->addSelect('m')
            ->addSelect('mc')
            ->addSelect('d')
            ->addSelect('i')
            ->addSelect('b')
            ->from(Entry::class, 'e')
            ->join('e.user', 'u')
            ->join('e.magazine', 'm')
            ->join('e.domain', 'd')
            ->leftJoin('u.avatar', 'ua')
            ->leftJoin('m.cover', 'mc')
            ->leftJoin('e.image', 'i')
            ->leftJoin('e.badges', 'b')
            ->where('e IN (?1)')
            ->setParameter(1, $entries)
            ->getQuery()
            ->getResult();

        if ($this->security->getUser()) {
            $this->_em->createQueryBuilder()
                ->select('PARTIAL e.{id}')
                ->addSelect('ev')
                ->addSelect('ef')
                ->from(Entry::class, 'e')
                ->leftJoin('e.favourites', 'ef')
                ->leftJoin('e.votes', 'ev')
                ->where('e IN (?1)')
                ->setParameter(1, $entries)
                ->getQuery()
                ->getResult();
        }
    }

    public function countEntryCommentsByMagazine(Magazine $magazine): int
    {
        return intval(
            $this->createQueryBuilder('e')
                ->select('sum(e.commentCount)')
                ->where('e.magazine = :magazine')
                ->setParameter('magazine', $magazine)
                ->getQuery()
                ->getSingleScalarResult()
        );
    }

    public function findToDelete(User $user, int $limit): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.visibility != :visibility')
            ->andWhere('e.user = :user')
            ->setParameters(['visibility' => Entry::VISIBILITY_SOFT_DELETED, 'user' => $user])
            ->orderBy('e.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRelatedByTag(string $tag, ?int $limit = 1): array
    {
        $qb = $this->createQueryBuilder('e');

        return $qb
            ->andWhere("JSONB_CONTAINS(e.tags, '\"".$tag."\"') = true")
            ->andWhere('e.visibility = :visibility')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameters(['visibility' => VisibilityInterface::VISIBILITY_VISIBLE])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRelatedByMagazine(string $name, ?int $limit = 1): array
    {
        $qb = $this->createQueryBuilder('e');

        return $qb->where('m.name LIKE :name OR m.title LIKE :title')
            ->andWhere('e.isAdult = false')
            ->andWhere('e.visibility = :visibility')
            ->join('e.magazine', 'm')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameters(
                ['name' => "%{$name}%", 'title' => "%{$name}%", 'visibility' => VisibilityInterface::VISIBILITY_VISIBLE]
            )
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLast(int $limit): array
    {
        $qb = $this->createQueryBuilder('e');

        return $qb
            ->where('e.isAdult = false')
            ->andWhere('e.visibility = :visibility')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameters(['visibility' => VisibilityInterface::VISIBILITY_VISIBLE])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findWithTags(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.tags IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
