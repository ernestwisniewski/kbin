<?php declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\Security\Core\Security;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\MagazineSubscription;
use Pagerfanta\PagerfantaInterface;
use App\PageView\EntryPageView;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use App\Entity\MagazineBlock;
use App\Entity\UserFollow;
use Pagerfanta\Pagerfanta;
use App\Entity\UserBlock;
use App\Entity\Magazine;
use App\Entity\Entry;

/**
 * @method Entry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entry[]    findAll()
 * @method Entry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryRepository extends ServiceEntityRepository
{
    const SORT_DEFAULT = 'aktywne';
    const TIME_DEFAULT = EntryPageView::TIME_ALL;
    const PER_PAGE = 25;

    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Entry::class);

        $this->security = $security;
    }

    public function findByCriteria(EntryPageView $criteria): PagerfantaInterface
    {
        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $this->getEntryQueryBuilder($criteria)
            )
        );

        try {
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
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

    private function filter(QueryBuilder $qb, EntryPageView $criteria): QueryBuilder
    {
        if ($criteria->magazine) {
            $qb->andWhere('e.magazine = :magazine')
                ->setParameter('magazine', $criteria->magazine);
        }

        if ($criteria->user) {
            $qb->andWhere('e.user = :user')
                ->setParameter('user', $criteria->user);
        }

        if ($criteria->subscribed) {
            $qb->andWhere(
                'e.magazine IN (SELECT IDENTITY(ms.magazine) FROM '.MagazineSubscription::class.' ms WHERE ms.user = :user) 
                OR 
                e.user IN (SELECT IDENTITY(uf.following) FROM '.UserFollow::class.' uf WHERE uf.follower = :user)
                OR
                e.user = :user'
            );
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($user = $this->security->getUser()) {
            $qb->andWhere(
                'e.user NOT IN (SELECT IDENTITY(ub.blocked) FROM '.UserBlock::class.' ub WHERE ub.blocker = :blocker)'
            );
            $qb->setParameter('blocker', $user);

            $qb->andWhere(
                'e.magazine NOT IN (SELECT IDENTITY(mb.magazine) FROM '.MagazineBlock::class.' mb WHERE mb.user = :magazineBlocker)'
            );
            $qb->setParameter('magazineBlocker', $user);
        }

        if ($criteria->type) {
            $qb->andWhere('e.type = :type')
                ->setParameter('type', $criteria->type);
        }

        switch ($criteria->sortOption) {
            case Criteria::SORT_HOT:
                $qb->addOrderBy('e.score', 'DESC');
                break;
            case Criteria::SORT_TOP:
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
                ->from(Entry::class, 'e')
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
}
