<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\BanNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BanNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method BanNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method BanNotification[]    findAll()
 * @method BanNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BanNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BanNotification::class);
    }
}
