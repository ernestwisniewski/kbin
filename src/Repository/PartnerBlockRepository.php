<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PartnerBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PartnerBlock>
 *
 * @method PartnerBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method PartnerBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method PartnerBlock[]    findAll()
 * @method PartnerBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartnerBlockRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartnerBlock::class);
    }

    public function findToDisplay(): ?PartnerBlock
    {
        $res = $this->createQueryBuilder('p')
            ->where('p.isActive = true')
            ->orderBy('p.lastActive', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (\count($res)) {
            return $res[0];
        }

        return null;
    }

    public function save(PartnerBlock $partner): void
    {
        $this->_em->persist($partner);
        $this->_em->flush();
    }
}
