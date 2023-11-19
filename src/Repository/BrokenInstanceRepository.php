<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BrokenInstance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BrokenInstance>
 *
 * @method BrokenInstance|null find($id, $lockMode = null, $lockVersion = null)
 * @method BrokenInstance|null findOneBy(array $criteria, array $orderBy = null)
 * @method BrokenInstance[]    findAll()
 * @method BrokenInstance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method findOneByHost(string|null $url)
 */
class BrokenInstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BrokenInstance::class);
    }
}
