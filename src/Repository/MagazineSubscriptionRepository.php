<?php declare(strict_types = 1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Moderator;

/**
 * @method Moderator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Moderator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Moderator[]    findAll()
 * @method Moderator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Moderator::class);
    }
}
