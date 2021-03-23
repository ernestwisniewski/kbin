<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\MessageNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MessageNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageNotification[]    findAll()
 * @method MessageNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageNotification::class);
    }
}
