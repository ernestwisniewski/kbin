<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MagazineBlock;
use App\Entity\Settings;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Settings>
 *
 * @method MagazineBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method MagazineBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method MagazineBlock[]    findAll()
 * @method MagazineBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MagazineBlock::class);
    }

    public function findMagazineBlocksIds(User $user): array
    {
        return array_column(
            $this->createQueryBuilder('mb')
                ->select('mbm.id')
                ->join('mb.magazine', 'mbm')
                ->where('mb.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult(),
            'id'
        );
    }
}
