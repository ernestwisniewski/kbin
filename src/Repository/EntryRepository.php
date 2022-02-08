<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\DomainBlock;
use App\Entity\DomainSubscription;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\Moderator;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use App\PageView\EntryPageView;
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
class EntryRepository extends ServiceEntityRepository
{
    const SORT_DEFAULT = 'hot';
    const  TIME_DEFAULT = EntryPageView::TIME_ALL;
    const PER_PAGE = 25;

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

        $this->hydrate(...$pagerfanta);

        return $pagerfanta;
    }

    private function getEntryQueryBuilder(EntryPageView $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.visibility = :e_visibility')
            ->leftJoin('e.magazine', 'm')
            ->andWhere('m.visibility = :m_visibility')
            ->setParameter('e_visibility', $criteria->visibility)
            ->setParameter('m_visibility', Magazine::VISIBILITY_VISIBLE);

        $this->addTimeClause($qb, $criteria);
        $this->addStickyClause($qb, $criteria);
        $this->filter($qb, $criteria);

        return $qb;
    }

    private function addTimeClause(QueryBuilder $qb, EntryPageView $criteria)
    {
        if ($criteria->time !== EntryPageView::TIME_ALL) {
            $since = $criteria->getSince();

            $qb->andWhere('e.createdAt > :time')
                ->setParameter('time', $since, Types::DATETIMETZ_IMMUTABLE);
        }
    }

    private function addStickyClause(QueryBuilder $qb, EntryPageView $criteria)
    {
        if ($criteria->stickiesFirst) {
            if ($criteria->page === 1) {
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
            $qb->andWhere($qb->expr()->like('e.tags', ':tag'))
                ->setParameter('tag', "%{$criteria->tag}%");
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
            $qb->andWhere('e.magazine IN (SELECT IDENTITY(mm.magazine) FROM '.Moderator::class.' mm WHERE mm.user = :user)');
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($user && (!$criteria->magazine || !$criteria->magazine->userIsModerator($user)) && !$criteria->moderated) {
            $qb->andWhere(
                'e.user NOT IN (SELECT IDENTITY(ub.blocked) FROM '.UserBlock::class.' ub WHERE ub.blocker = :blocker)'
            );
            $qb->setParameter('blocker', $user);

            $qb->andWhere(
                'e.magazine NOT IN (SELECT IDENTITY(mb.magazine) FROM '.MagazineBlock::class.' mb WHERE mb.user = :magazineBlocker)'
            );
            $qb->setParameter('magazineBlocker', $user);

            if (!$criteria->domain) {
                $qb->andWhere(
                    'e.domain NOT IN (SELECT IDENTITY(db.domain) FROM '.DomainBlock::class.' db WHERE db.user = :domainBlocker)'
                );
                $qb->setParameter('domainBlocker', $user);
            }
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
            case Criteria::SORT_NEW:
            default:
                $qb->addOrderBy('e.id', 'DESC');
        }

        $qb->addOrderBy('e.createdAt', 'DESC');

        return $qb;
    }

    public function hydrate(Entry ...$entries): void
    {
        $this->_em->createQueryBuilder()
            ->select('PARTIAL e.{id}')
            ->addSelect('u')
            ->addSelect('m')
            ->addSelect('d')
            ->addSelect('i')
            ->addSelect('b')
            ->from(Entry::class, 'e')
            ->join('e.user', 'u')
            ->join('e.magazine', 'm')
            ->join('e.domain', 'd')
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
}
