<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\ForumSubscription;
use App\Entity\Magazine;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\Submission;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use App\PageView\EntryPageView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\PagerfantaInterface;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use App\Entity\Entry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Entry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entry[]    findAll()
 * @method Entry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryRepository extends ServiceEntityRepository
{
    const SORT_DEFAULT = 'aktywne';
    const TIME_DEFAULT = Criteria::TIME_ALL;
    const PER_PAGE = 25;

    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Entry::class);

        $this->security = $security;
    }

    public function findByCriteria(Criteria $criteria): PagerfantaInterface
    {
        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $this->getEntryQueryBuilder($criteria)
            )
        );

        $pagerfanta->setMaxPerPage(self::PER_PAGE);

        try {
            $pagerfanta->setCurrentPage($criteria->getPage());
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $this->hydrate(...$pagerfanta);

        return $pagerfanta;
    }

    private function getEntryQueryBuilder(Criteria $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.visibility = :e_visibility')
            ->leftJoin('e.magazine', 'm')
            ->andWhere('m.visibility = :m_visibility')
            ->setParameter('e_visibility', $criteria->getVisibility())
            ->setParameter('m_visibility', Magazine::VISIBILITY_VISIBLE);

        $this->addTimeClause($qb, $criteria);
        $this->filter($qb, $criteria);

        return $qb;
    }

    private function filter(QueryBuilder $qb, Criteria $criteria): QueryBuilder
    {
        if ($criteria->getMagazine()) {
            $qb->andWhere('e.magazine = :magazine')
                ->setParameter('magazine', $criteria->getMagazine());
        }

        if ($criteria->getUser()) {
            $qb->andWhere('e.user = :user')
                ->setParameter('user', $criteria->getUser());
        }

        if ($criteria->isSubscribed()) {
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

        switch ($criteria->getSortOption()) {
            case Criteria::SORT_HOT:
                $qb->orderBy('e.score', 'DESC');
                break;
            case Criteria::SORT_TOP:
                $qb->orderBy('e.ranking', 'DESC');
                break;
            case Criteria::SORT_COMMENTED:
                $qb->orderBy('e.commentCount', 'DESC');
                break;
            case Criteria::SORT_ACTIVE:
                $qb->orderBy('e.lastActive', 'DESC');
                break;
            case Criteria::SORT_NEW:
            default:
                $qb->orderBy('e.id', 'DESC');
        }

        return $qb;
    }


    private function addTimeClause(QueryBuilder $qb, Criteria $criteria)
    {
        if ($criteria->getTime() !== EntryPageView::TIME_ALL) {
            $since = new \DateTimeImmutable('@'.time());

            switch ($criteria->getTime()) {
                case EntryPageView::TIME_YEAR:
                    $since = $since->modify('-1 year');
                    break;
                case EntryPageView::TIME_MONTH:
                    $since = $since->modify('-1 month');
                    break;
                case EntryPageView::TIME_WEEK:
                    $since = $since->modify('-1 week');
                    break;
                case EntryPageView::TIME_DAY:
                    $since = $since->modify('-1 day');
                    break;
                case EntryPageView::TIME_12_HOURS:
                    $since = $since->modify('-12 hours');
                    break;
                case EntryPageView::TIME_6_HOURS:
                    $since = $since->modify('-6 hours');
                    break;
                default:
                    throw new \LogicException();
            }

            $qb->andWhere('e.createdAt > :time')
                ->setParameter('time', $since, Types::DATETIMETZ_IMMUTABLE);
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
            ->from(Entry::class, 'e')
            ->join('e.user', 'u')
            ->join('e.magazine', 'm')
            ->join('e.domain', 'd')
            ->leftJoin('e.image', 'i')
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

    public function countCommentsByMagazine(Magazine $magazine): int
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
