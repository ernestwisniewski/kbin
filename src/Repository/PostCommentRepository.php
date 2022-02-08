<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\PostComment;
use App\Entity\User;
use App\PageView\PostCommentPageView;
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
 * @method PostComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostComment[]    findAll()
 * @method PostComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostCommentRepository extends ServiceEntityRepository
{
    const PER_PAGE = 200;

    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, PostComment::class);

        $this->security = $security;
    }

    public function findByCriteria(PostCommentPageView $criteria): PagerfantaInterface
    {
        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $this->getCommentQueryBuilder($criteria)
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

    private function getCommentQueryBuilder(Criteria $criteria): QueryBuilder
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

    private function filter(QueryBuilder $qb, Criteria $criteria)
    {
        if ($criteria->post) {
            $qb->andWhere('c.post = :post')
                ->setParameter('post', $criteria->post);
        }

        if ($criteria->magazine) {
            $qb->join('c.post', 'p', Join::WITH, 'p.magazine = :magazine');
            $qb->setParameter('magazine', $criteria->magazine);
        }

        if ($criteria->user) {
            $qb->andWhere('c.user = :user')
                ->setParameter('user', $criteria->user);
        }

        switch ($criteria->sortOption) {
            case Criteria::SORT_HOT:
                $qb->orderBy('c.upVotes', 'DESC');
                break;
            default:
                $qb->addOrderBy('c.id', 'ASC');
        }

        $qb->addOrderBy('c.createdAt', 'DESC');
    }

    public function hydrate(PostComment ...$comment): void
    {
        $this->_em->createQueryBuilder()
            ->select('PARTIAL c.{id}')
            ->addSelect('u')
            ->addSelect('m')
            ->addSelect('i')
            ->from(PostComment::class, 'c')
            ->join('c.user', 'u')
            ->join('c.magazine', 'm')
            ->leftJoin('c.image', 'i')
            ->where('c IN (?1)')
            ->setParameter(1, $comment)
            ->getQuery()
            ->getResult();

        if ($this->security->getUser()) {
            $this->_em->createQueryBuilder()
                ->select('PARTIAL c.{id}')
                ->addSelect('cv')
                ->addSelect('cf')
                ->from(PostComment::class, 'c')
                ->leftJoin('c.votes', 'cv')
                ->leftJoin('c.favourites', 'cf')
                ->where('c IN (?1)')
                ->setParameter(1, $comment)
                ->getQuery()
                ->getResult();
        }
    }

    public function findToDelete(User $user, int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.visibility != :visibility')
            ->andWhere('c.user = :user')
            ->setParameters(['visibility' => PostComment::VISIBILITY_SOFT_DELETED, 'user' => $user])
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
