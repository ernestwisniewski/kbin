<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Settings;
use App\Entity\User;
use App\Entity\UserBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Settings>
 *
 * @method UserBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBlock[]    findAll()
 * @method UserBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBlock::class);
    }

    public function findUserBlocksIds(User $user): array
    {
        return array_column(
            $this->createQueryBuilder('ub')
                ->select('ubu.id')
                ->join('ub.blocked', 'ubu')
                ->where('ub.blocker = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult(),
            'id'
        );
    }
}
