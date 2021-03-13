<?php declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\EntryCommentNotification;

/**
 * @method EntryCommentNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryCommentNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryCommentNotification[]    findAll()
 * @method EntryCommentNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryCommentNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryCommentNotification::class);
    }
}
