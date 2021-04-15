<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\EntryCommentCreatedNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntryCommentCreatedNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryCommentCreatedNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryCommentCreatedNotification[]    findAll()
 * @method EntryCommentCreatedNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryCommentNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryCommentCreatedNotification::class);
    }
}
