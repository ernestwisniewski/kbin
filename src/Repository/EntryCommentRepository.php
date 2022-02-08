<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\EntryComment;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;

/**
 * @method EntryComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryComment[]    findAll()
 * @method EntryComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryCommentRepository extends ServiceEntityRepository
{
    const SORT_DEFAULT = 'active';
    const PER_PAGE = 15;

    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, EntryComment::class);

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

        return $pagerfanta;
    }

    private function getEntryQueryBuilder(Criteria $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        $this->addTimeClause($qb, $criteria);
        $this->filter($qb, $criteria);

        return $qb;
    }

    private function addTimeClause(QueryBuilder $qb, Criteria $criteria): void
    {
        if ($criteria->time !== Criteria::TIME_ALL) {
            $since = $criteria->getSince();

            $qb->andWhere('c.createdAt > :time')
                ->setParameter('time', $since, Types::DATETIMETZ_IMMUTABLE);
        }
    }

    private function filter(QueryBuilder $qb, Criteria $criteria): QueryBuilder
    {
        $user = $this->security->getUser();

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
            $qb->leftJoin('c.entry', 'e')
                ->andWhere('e.visibility = :visible')
                ->setParameter('visible', VisibilityInterface::VISIBILITY_VISIBLE);
        }

        if ($criteria->user) {
            $qb->andWhere('c.user = :user')
                ->setParameter('user', $criteria->user);
        }

        if ($criteria->tag) {
            $qb->andWhere($qb->expr()->like('c.tags', ':tag'))
                ->setParameter('tag', "%{$criteria->tag}%");
        }

        if ($criteria->domain) {
            $qb->andWhere('ced.name = :domain')
                ->join('c.entry', 'ce')
                ->join('ce.domain', 'ced')
                ->setParameter('domain', $criteria->domain);
        }

        if ($criteria->subscribed) {
            $qb->andWhere(
                'c.magazine IN (SELECT IDENTITY(ms.magazine) FROM '.MagazineSubscription::class.' ms WHERE ms.user = :follower) 
                OR 
                c.user IN (SELECT IDENTITY(uf.following) FROM '.UserFollow::class.' uf WHERE uf.follower = :follower)
                OR
                c.user = :user'
            );
            $qb->setParameter('follower', $this->security->getUser());
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($user = $this->security->getUser()) {
            $qb->andWhere(
                'c.user NOT IN (SELECT IDENTITY(ub.blocked) FROM '.UserBlock::class.' ub WHERE ub.blocker = :blocker)'
            );
            $qb->setParameter('blocker', $user);

            $qb->andWhere(
                'c.magazine NOT IN (SELECT IDENTITY(mb.magazine) FROM '.MagazineBlock::class.' mb WHERE mb.user = :magazineBlocker)'
            );
            $qb->setParameter('magazineBlocker', $user);
        }

        if ($criteria->onlyParents) {
            $qb->andWhere('c.parent IS NULL');
        }

        if(!$user || $user->hideAdult) {
            $qb->join('e.magazine', 'm')
                ->andWhere('m.isAdult = :isAdult')
                ->andWhere('e.isAdult = :isAdult')
                ->setParameter('isAdult', false);
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
            default:
                $qb->addOrderBy('c.lastActive', 'DESC')
                    ->addOrderBy('c.id', 'DESC');
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
            ->leftJoin('cc.user', 'ccu')
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
}
