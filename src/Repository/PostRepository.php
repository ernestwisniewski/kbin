<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Magazine;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\Moderator;
use App\Entity\Post;
use App\Entity\PostFavourite;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use App\Repository\Contract\TagRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository implements TagRepositoryInterface
{
    public const PER_PAGE = 15;
    public const SORT_DEFAULT = 'hot';

    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Post::class);

        $this->security = $security;
    }

    public function findByCriteria(Criteria $criteria): PagerfantaInterface
    {
        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $this->getEntryQueryBuilder($criteria)
            )
        );

        try {
            $pagerfanta->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
            $pagerfanta->setCurrentPage($criteria->page);
            if (!$criteria->magazine) {
                $pagerfanta->setMaxNbPages(5000);
            }
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $this->hydrate(...$pagerfanta->getCurrentPageResults());

        return $pagerfanta;
    }

    private function getEntryQueryBuilder(Criteria $criteria): QueryBuilder
    {
        $user = $this->security->getUser();

        $qb = $this->createQueryBuilder('p')
            ->where('p.visibility = :p_visibility')
            ->join('p.magazine', 'm')
            ->andWhere('m.visibility = :m_visibility');

        if ($user && VisibilityInterface::VISIBILITY_VISIBLE === $criteria->visibility) {
            $qb->orWhere(
                'p.user IN (SELECT IDENTITY(puf.following) FROM '.UserFollow::class.' puf WHERE puf.follower = :pUser AND p.visibility = :pVisibility)'
            )
                ->setParameter('pUser', $user)
                ->setParameter('pVisibility', VisibilityInterface::VISIBILITY_PRIVATE);
        }

        $qb->setParameter('p_visibility', $criteria->visibility)
            ->setParameter('m_visibility', VisibilityInterface::VISIBILITY_VISIBLE);

        $this->addTimeClause($qb, $criteria);
        $this->filter($qb, $criteria);

        return $qb;
    }

    private function addTimeClause(QueryBuilder $qb, Criteria $criteria): void
    {
        if (Criteria::TIME_ALL !== $criteria->time) {
            $since = $criteria->getSince();

            $qb->andWhere('p.createdAt > :time')
                ->setParameter('time', $since, Types::DATETIMETZ_IMMUTABLE);
        }
    }

    private function filter(QueryBuilder $qb, Criteria $criteria): QueryBuilder
    {
        $user = $this->security->getUser();

        if ($criteria->federation === Criteria::AP_LOCAL) {
            $qb->andWhere('p.apId IS NULL');
        }

        if ($criteria->magazine) {
            $qb->andWhere('p.magazine = :magazine')
                ->setParameter('magazine', $criteria->magazine);
        }

        if ($criteria->user) {
            $qb->andWhere('p.user = :user')
                ->setParameter('user', $criteria->user);
        }

        if ($criteria->tag) {
            $qb->andWhere("JSONB_CONTAINS(p.tags, '\"".$criteria->tag."\"') = true");
        }

        if ($criteria->subscribed) {
            $qb->andWhere(
                'p.magazine IN (SELECT IDENTITY(ms.magazine) FROM '.MagazineSubscription::class.' ms WHERE ms.user = :user) 
                OR 
                p.user IN (SELECT IDENTITY(uf.following) FROM '.UserFollow::class.' uf WHERE uf.follower = :user)
                OR
                p.user = :user'
            );
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($criteria->moderated) {
            $qb->andWhere(
                'p.magazine IN (SELECT IDENTITY(mm.magazine) FROM '.Moderator::class.' mm WHERE mm.user = :user)'
            );
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($criteria->favourite) {
            $qb->andWhere(
                'p.id IN (SELECT IDENTITY(pf.post) FROM '.PostFavourite::class.' pf WHERE pf.user = :user)'
            );
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($user && (!$criteria->magazine || !$criteria->magazine->userIsModerator($user)) && !$criteria->moderated) {
            $qb->andWhere(
                'p.user NOT IN (SELECT IDENTITY(ub.blocked) FROM '.UserBlock::class.' ub WHERE ub.blocker = :blocker)'
            );
            $qb->setParameter('blocker', $user);

            $qb->andWhere(
                'p.magazine NOT IN (SELECT IDENTITY(mb.magazine) FROM '.MagazineBlock::class.' mb WHERE mb.user = :magazineBlocker)'
            );
            $qb->setParameter('magazineBlocker', $user);
        }

        if (!$user || $user->hideAdult) {
            $qb->andWhere('m.isAdult = :isAdult')
                ->andWhere('p.isAdult = :isAdult')
                ->setParameter('isAdult', false);
        }

        switch ($criteria->sortOption) {
            case Criteria::SORT_HOT:
                $qb->orderBy('p.ranking', 'DESC');
                break;
            case Criteria::SORT_TOP:
                $qb->orderBy('p.score', 'DESC');
                break;
            case Criteria::SORT_COMMENTED:
                $qb->orderBy('p.commentCount', 'DESC');
                break;
            case Criteria::SORT_ACTIVE:
                $qb->orderBy('p.lastActive', 'DESC');
                break;
            default:
        }

        $qb->addOrderBy('p.createdAt', $criteria->sortOption === Criteria::SORT_OLD ? 'ASC' : 'DESC');
        $qb->addOrderBy('p.id', 'DESC');

        return $qb;
    }

    public function hydrate(Post ...$posts): void
    {
        $this->_em->createQueryBuilder()
            ->select('PARTIAL p.{id}')
            ->addSelect('u')
            ->addSelect('ua')
            ->addSelect('m')
            ->addSelect('i')
            ->from(Post::class, 'p')
            ->join('p.user', 'u')
            ->join('p.magazine', 'm')
            ->leftJoin('u.avatar', 'ua')
            ->leftJoin('p.image', 'i')
            ->where('p IN (?1)')
            ->setParameter(1, $posts)
            ->getQuery()
            ->getResult();

        if ($this->security->getUser()) {
            $this->_em->createQueryBuilder()
                ->select('PARTIAL p.{id}')
                ->addSelect('pv')
                ->addSelect('pf')
                ->from(Post::class, 'p')
                ->leftJoin('p.votes', 'pv')
                ->leftJoin('p.favourites', 'pf')
                ->where('p IN (?1)')
                ->setParameter(1, $posts)
                ->getQuery()
                ->getResult();
        }
    }

    public function countPostsByMagazine(Magazine $magazine)
    {
        return intval(
            $this->createQueryBuilder('p')
                ->select('count(p.id)')
                ->where('p.magazine = :magazine')
                ->andWhere('p.visibility = :visibility')
                ->setParameter('magazine', $magazine)
                ->setParameter('visibility', VisibilityInterface::VISIBILITY_VISIBLE)
                ->getQuery()
                ->getSingleScalarResult()
        );
    }

    public function countPostCommentsByMagazine(Magazine $magazine)
    {
        return intval(
            $this->createQueryBuilder('p')
                ->select('sum(p.commentCount)')
                ->where('p.magazine = :magazine')
                ->setParameter('magazine', $magazine)
                ->getQuery()
                ->getSingleScalarResult()
        );
    }

    public function findToDelete(User $user, int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.visibility != :visibility')
            ->andWhere('p.user = :user')
            ->setParameters(['visibility' => Post::VISIBILITY_SOFT_DELETED, 'user' => $user])
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRelatedByTag(string $tag, ?int $limit = 1): array
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->andWhere("JSONB_CONTAINS(p.tags, '\"".$tag."\"') = true")
            ->andWhere('p.isAdult = false')
            ->andWhere('p.visibility = :visibility')
            ->andWhere('m.name != :name')
            ->andWhere('m.isAdult = false')
            ->join('p.magazine', 'm')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameters(['visibility' => VisibilityInterface::VISIBILITY_VISIBLE, 'name' => $tag])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRelatedByMagazine(string $name, ?int $limit = 1): array
    {
        $qb = $this->createQueryBuilder('p');

        return $qb->where('m.name LIKE :name OR m.title LIKE :title')
            ->andWhere('m.isAdult = false')
            ->andWhere('p.visibility = :visibility')
            ->andWhere('p.isAdult = false')
            ->join('p.magazine', 'm')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameters(
                ['name' => "%{$name}%", 'title' => "%{$name}%", 'visibility' => VisibilityInterface::VISIBILITY_VISIBLE]
            )
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLast(int $limit = 1): array
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->where('p.isAdult = false')
            ->andWhere('p.visibility = :visibility')
            ->andWhere('m.isAdult = false')
            ->join('p.magazine', 'm')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameters(['visibility' => VisibilityInterface::VISIBILITY_VISIBLE])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findFederated()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.apId IS NOT NULL')
            ->andWhere('p.visibility = :visibility')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameter('visibility', VisibilityInterface::VISIBILITY_VISIBLE)
            ->getQuery()
            ->getResult();
    }

    public function findTaggedFederatedInRandomMagazine()
    {
        return $this->createQueryBuilder('p')
            ->join('p.magazine', 'm')
            ->andWhere('m.name = :magazine')
            ->andWhere('p.apId IS NOT NULL')
            ->andWhere('p.visibility = :visibility')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameters(
                ['visibility' => VisibilityInterface::VISIBILITY_VISIBLE, 'magazine' => 'random']
            )
            ->getQuery()
            ->getResult();
    }

    public function findUsers(Magazine $magazine, ?bool $federated = false): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('u.id, COUNT(p.id) as count')
            ->groupBy('u.id')
            ->join('p.user', 'u')
            ->join('p.magazine', 'm')
            ->andWhere('p.magazine = :magazine')
            ->andWhere('p.visibility = :visibility')
            ->andWhere('u.about != :emptyString')
            ->andWhere('u.isBanned = false');

        if ($federated) {
            $qb->andWhere('u.apId IS NOT NULL')
                ->andWhere('u.apDiscoverable = true');
        } else {
            $qb->andWhere('u.apId IS NULL');
        }

        return $qb->orderBy('count', 'DESC')
            ->setParameters(
                ['magazine' => $magazine, 'emptyString' => '', 'visibility' => VisibilityInterface::VISIBILITY_VISIBLE]
            )
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function findWithTags(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.tags IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
