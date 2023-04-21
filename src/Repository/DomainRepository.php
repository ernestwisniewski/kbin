<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Domain;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\Collections\CollectionAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Domain|null find($id, $lockMode = null, $lockVersion = null)
 * @method Domain|null findOneBy(array $criteria, array $orderBy = null)
 * @method Domain|null findOneByName(string $name)
 * @method Domain[]    findAll()
 * @method Domain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomainRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 100;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Domain::class);
    }

    public function findAllPaginated(?int $page): PagerfantaInterface
    {
        $qb = $this->createQueryBuilder('d');

        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $qb
            )
        );

        try {
            $pagerfanta->setMaxPerPage($criteria->perPage ?? self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    public function findSubscribedDomains(int $page, User $user): PagerfantaInterface
    {
        $pagerfanta = new Pagerfanta(
            new CollectionAdapter(
                $user->subscribedDomains
            )
        );

        try {
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    public function findBlockedDomains(int $page, User $user): PagerfantaInterface
    {
        $pagerfanta = new Pagerfanta(
            new CollectionAdapter(
                $user->blockedDomains
            )
        );

        try {
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }
}
