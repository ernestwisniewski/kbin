<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MagazineOwnershipRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method MagazineOwnershipRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method MagazineOwnershipRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method MagazineOwnershipRequest|null findOneByName(string $name)
 * @method MagazineOwnershipRequest[]    findAll()
 * @method MagazineOwnershipRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineOwnershipRequestRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MagazineOwnershipRequest::class);
    }

    public function findAllPaginated(?int $page): PagerfantaInterface
    {
        $qb = $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'ASC');

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
}
