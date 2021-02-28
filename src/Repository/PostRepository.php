<?php

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\Post;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use App\PageView\PostPageView;
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
 * @method UserFollow|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFollow|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFollow[]    findAll()
 * @method UserFollow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    const PER_PAGE = 15;

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

        $pagerfanta->setMaxPerPage(self::PER_PAGE);

        try {
            $pagerfanta->setCurrentPage($criteria->getPage());
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

//        $this->hydrate(...$pagerfanta);

        return $pagerfanta;
    }

    private function getEntryQueryBuilder(Criteria $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.visibility = :p_visibility')
            ->leftJoin('p.magazine', 'm')
            ->andWhere('m.visibility = :m_visibility')
            ->setParameter('p_visibility', $criteria->getVisibility())
            ->setParameter('m_visibility', Magazine::VISIBILITY_VISIBLE);

        $this->addTimeClause($qb, $criteria);
        $this->filter($qb, $criteria);

        return $qb;
    }


    private function addTimeClause(QueryBuilder $qb, Criteria $criteria):void
    {
        if ($criteria->getTime() !== Criteria::TIME_ALL) {
            $since = $criteria->getSince();

            $qb->andWhere('p.createdAt > :time')
                ->setParameter('time', $since, Types::DATETIMETZ_IMMUTABLE);
        }
    }

    private function filter(QueryBuilder $qb, Criteria $criteria): QueryBuilder
    {
        if ($criteria->getMagazine()) {
            $qb->andWhere('p.magazine = :magazine')
                ->setParameter('magazine', $criteria->getMagazine());
        }

        if ($criteria->getUser()) {
            $qb->andWhere('p.user = :user')
                ->setParameter('user', $criteria->getUser());
        }

        if ($criteria->isSubscribed()) {
            $qb->andWhere(
                'p.magazine IN (SELECT IDENTITY(ms.magazine) FROM '.MagazineSubscription::class.' ms WHERE ms.user = :user) 
                OR 
                p.user IN (SELECT IDENTITY(uf.following) FROM '.UserFollow::class.' uf WHERE uf.follower = :user)
                OR
                p.user = :user'
            );
            $qb->setParameter('user', $this->security->getUser());
        }

        if ($user = $this->security->getUser()) {
            $qb->andWhere(
                'p.user NOT IN (SELECT IDENTITY(ub.blocked) FROM '.UserBlock::class.' ub WHERE ub.blocker = :blocker)'
            );
            $qb->setParameter('blocker', $user);

            $qb->andWhere(
                'p.magazine NOT IN (SELECT IDENTITY(mb.magazine) FROM '.MagazineBlock::class.' mb WHERE mb.user = :magazineBlocker)'
            );
            $qb->setParameter('magazineBlocker', $user);
        }

        switch ($criteria->getSortOption()) {
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

        return $qb;
    }

}
