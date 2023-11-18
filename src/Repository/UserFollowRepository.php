<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Settings;
use App\Entity\User;
use App\Entity\UserFollow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Settings>
 *
 * @method UserFollow|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFollow|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFollow[]    findAll()
 * @method UserFollow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFollowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFollow::class);
    }

    public function findUserFollowIds(User $user): array
    {
        return array_column(
            $this->createQueryBuilder('uf')
                ->select('ufu.id')
                ->join('uf.following', 'ufu')
                ->where('uf.follower = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult(),
            'id'
        );
    }
}
