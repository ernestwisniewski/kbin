<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Award;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Award|null find($id, $lockMode = null, $lockVersion = null)
 * @method Award|null findOneBy(array $criteria, array $orderBy = null)
 * @method Award|null findOneByName(string $name)
 * @method Award[]    findAll()
 * @method Award[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AwardRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 100;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Award::class);
    }
}
