<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\ModeratorRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**`
 * @method ModeratorRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModeratorRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModeratorRequest|null findOneByName(string $name)
 * @method ModeratorRequest[]    findAll()
 * @method ModeratorRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModeratorRequestRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModeratorRequest::class);
    }

    public function findAllPaginated(Magazine $magazine, ?int $page): PagerfantaInterface
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.magazine = :magazine')
            ->orderBy('r.createdAt', 'ASC')
            ->setParameter('magazine', $magazine);

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
