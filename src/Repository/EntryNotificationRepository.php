<?php declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\EntryNotification;

/**
 * @method EntryNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryNotification[]    findAll()
 * @method EntryNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryNotification::class);
    }
}
