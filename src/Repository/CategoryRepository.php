<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category|null findOneByName(string $name)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findAllPublic(int $page = 1): Pagerfanta
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.isPrivate = false')
            ->orderBy('c.isOfficial', 'DESC')
            ->addOrderBy('c.subscriptionsCount', 'ASC')
            ->getQuery();

        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $query
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

    public function findRandom(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT c.id FROM category c
            WHERE c.is_private = false
            ORDER BY random()
            LIMIT 5
            ';
        $stmt = $conn->prepare($sql);

        $stmt = $stmt->executeQuery();
        $ids = $stmt->fetchAllAssociative();

        return $this->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function findRelated(string $category): array
    {
        $category = strtolower($category);

        return $this->createQueryBuilder('c')
            ->andWhere('LOWER(c.name) LIKE :category OR LOWER(c.description) LIKE :category')
            ->andWhere('c.isPrivate = false')
            ->setParameter('category', "%{$category}%")
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
}
