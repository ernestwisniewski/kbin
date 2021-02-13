<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\MagazineSubscription;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use App\Entity\EntryComment;
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
    const SORT_DEFAULT = 'najnowsze';
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

        $pagerfanta->setMaxPerPage(self::PER_PAGE);

        try {
            $pagerfanta->setCurrentPage($criteria->getPage());
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    private function getEntryQueryBuilder(Criteria $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('cc');

        $this->filter($qb, $criteria);

        return $qb;
    }

    private function filter(QueryBuilder $qb, Criteria $criteria): QueryBuilder
    {
        $qb->andWhere(
            'c.user NOT IN (SELECT IDENTITY(ub.blocked) FROM '.UserBlock::class.' ub WHERE ub.blocker = :user)
            AND
            cc.user NOT IN (SELECT IDENTITY(ubp.blocked) FROM '.UserBlock::class.' ubp WHERE ubp.blocker = :user)'
        );
        $qb->setParameter('user', $this->security->getUser());

        if ($criteria->getEntry()) {
            $qb->andWhere('c.entry = :entry')
                ->setParameter('entry', $criteria->getEntry());
        }

        if ($criteria->getMagazine()) {
            $qb->join('c.entry', 'e', Join::WITH, 'e.magazine = :magazine');
            $qb->setParameter('magazine', $criteria->getMagazine());
        }

        if ($criteria->getUser()) {
            $qb->andWhere('c.user = :user')
                ->setParameter('user', $criteria->getUser());
        }

        if ($criteria->isSubscribed()) {
            $qb->andWhere(
                'c.magazine IN (SELECT IDENTITY(ms.magazine) FROM '.MagazineSubscription::class.' ms WHERE ms.user = :user) 
                OR 
                c.user IN (SELECT IDENTITY(uf.following) FROM '.UserFollow::class.' uf WHERE uf.follower = :user)
                OR
                c.user = :user'
            );
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($criteria->isOnlyParents()) {
            $qb->andWhere('c.parent IS NULL');
        }

        switch ($criteria->getSortOption()) {
            case Criteria::SORT_HOT:
                $qb->orderBy('c.upVotes', 'DESC');
                break;
            default:
                $qb->orderBy('c.id', 'DESC');
        }

        $qb->leftJoin('c.children', 'cc');

        return $qb;
    }

    public function hydrate(EntryComment ...$comments): void
    {
        $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id}')
            ->addSelect('u')
            ->addSelect('e')
            ->addSelect('v')
            ->addSelect('em')
            ->join('c.user', 'u')
            ->join('c.entry', 'e')
            ->join('c.votes', 'v')
            ->join('e.magazine', 'em')
            ->where('c IN (?1)')
            ->setParameter(1, $comments)
            ->getQuery()
            ->execute();

        $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id}')
            ->addSelect('cc')
            ->addSelect('ccu')
            ->addSelect('ccv')
            ->leftJoin('c.children', 'cc')
            ->leftJoin('cc.user', 'ccu')
            ->leftJoin('cc.votes', 'ccv')
            ->where('c IN (?1)')
            ->setParameter(1, $comments)
            ->getQuery()
            ->execute();
    }

    public function hydrateChildren(EntryComment ...$comments): void
    {
        $children = $this->createQueryBuilder('c')
            ->andWhere('c.root IN (:ids)')
            ->setParameter('ids', $comments)
            ->getQuery()->getResult();

        $this->hydrate(...$children);
    }
}
