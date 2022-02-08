<?php declare(strict_types = 1);

namespace App\Repository;


use App\Entity\Magazine;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\Moderator;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
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
 * @method UserFollow|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFollow|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFollow[]    findAll()
 * @method UserFollow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    const PER_PAGE = 15;
    const SORT_DEFAULT = 'active';

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
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $this->hydrate(...$pagerfanta);

        return $pagerfanta;
    }

    private function getEntryQueryBuilder(Criteria $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.visibility = :p_visibility')
            ->leftJoin('p.magazine', 'm')
            ->andWhere('m.visibility = :m_visibility')
            ->setParameter('p_visibility', $criteria->visibility)
            ->setParameter('m_visibility', Magazine::VISIBILITY_VISIBLE);

        $this->addTimeClause($qb, $criteria);
        $this->filter($qb, $criteria);

        return $qb;
    }


    private function addTimeClause(QueryBuilder $qb, Criteria $criteria): void
    {
        if ($criteria->time !== Criteria::TIME_ALL) {
            $since = $criteria->getSince();

            $qb->andWhere('p.createdAt > :time')
                ->setParameter('time', $since, Types::DATETIMETZ_IMMUTABLE);
        }
    }

    private function filter(QueryBuilder $qb, Criteria $criteria): QueryBuilder
    {
        $user = $this->security->getUser();

        if ($criteria->magazine) {
            $qb->andWhere('p.magazine = :magazine')
                ->setParameter('magazine', $criteria->magazine);
        }

        if ($criteria->user) {
            $qb->andWhere('p.user = :user')
                ->setParameter('user', $criteria->user);
        }

        if ($criteria->tag) {
            $qb->andWhere($qb->expr()->like('p.tags', ':tag'))
                ->setParameter('tag', "%{$criteria->tag}%");
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
            $qb->andWhere('p.magazine IN (SELECT IDENTITY(mm.magazine) FROM '.Moderator::class.' mm WHERE mm.user = :user)');
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

        if(!$user || $user->hideAdult) {
            $qb->andWhere('m.isAdult = :isAdult')
                ->andWhere('p.isAdult = :isAdult')
                ->setParameter('isAdult', false);
        }

        switch ($criteria->sortOption) {
            case Criteria::SORT_HOT:
                $qb->orderBy('p.score', 'DESC');
                break;
            case Criteria::SORT_TOP:
                $qb->orderBy('p.ranking', 'DESC');
                break;
            case Criteria::SORT_COMMENTED:
                $qb->orderBy('p.commentCount', 'DESC');
                break;
            case Criteria::SORT_ACTIVE:
                $qb->orderBy('p.lastActive', 'DESC');
                break;
            case Criteria::SORT_NEW:
            default:
                $qb->orderBy('p.id', 'DESC');
        }

        $qb->addOrderBy('p.createdAt', 'DESC');

        return $qb;
    }

    public function hydrate(Post ...$posts): void
    {
        $this->_em->createQueryBuilder()
            ->select('PARTIAL p.{id}')
            ->addSelect('u')
            ->addSelect('m')
            ->addSelect('i')
            ->from(Post::class, 'p')
            ->join('p.user', 'u')
            ->join('p.magazine', 'm')
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

    public function countPostCommentsByMagazine(?Magazine $magazine)
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
}
