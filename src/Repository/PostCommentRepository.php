<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\PageView\PostCommentPageView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use App\Entity\PostComment;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method PostComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostComment[]    findAll()
 * @method PostComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostCommentRepository extends ServiceEntityRepository
{
    const PER_PAGE = 15;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostComment::class);
    }

    public function findByCriteria(PostCommentPageView $criteria): PagerfantaInterface
    {
        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $this->getCommentQueryBuilder($criteria)
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

    private function getCommentQueryBuilder(Criteria $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        $this->addTimeClause($qb, $criteria);
        $this->filter($qb, $criteria);

        return $qb;
    }

    private function addTimeClause(QueryBuilder $qb, Criteria $criteria):void
    {
        if ($criteria->getTime() !== Criteria::TIME_ALL) {
            $since = $criteria->getSince();

            $qb->andWhere('c.createdAt > :time')
                ->setParameter('time', $since, Types::DATETIMETZ_IMMUTABLE);
        }
    }

    public function hydrate(array $comments)
    {
    }

    private function filter(QueryBuilder $qb, Criteria $criteria)
    {
        if ($criteria->getPost()) {
            $qb->andWhere('c.post = :post')
                ->setParameter('post', $criteria->getPost());
        }

        if ($criteria->getMagazine()) {
            $qb->join('c.post', 'p', Join::WITH, 'p.magazine = :magazine');
            $qb->setParameter('magazine', $criteria->getMagazine());
        }

        if ($criteria->getUser()) {
            $qb->andWhere('c.user = :user')
                ->setParameter('user', $criteria->getUser());
        }

        switch ($criteria->getSortOption()) {
            case Criteria::SORT_HOT:
                $qb->orderBy('c.upVotes', 'DESC');
                break;
            default:
                $qb->addOrderBy('c.lastActive', 'DESC')
                    ->addOrderBy('c.id', 'DESC');
        }
    }
}
