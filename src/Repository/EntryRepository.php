<?php declare(strict_types = 1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\PagerfantaInterface;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use App\Entity\Entry;

/**
 * @method Entry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entry[]    findAll()
 * @method Entry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryRepository extends ServiceEntityRepository
{
    const PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entry::class);
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
        $qb = $this->createQueryBuilder('e');

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

        return $qb;
    }
}
