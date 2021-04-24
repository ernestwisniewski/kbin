<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\EntryCreatedNotification;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntryCreatedNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryCreatedNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryCreatedNotification[]    findAll()
 * @method EntryCreatedNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryCreatedNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryCreatedNotification::class);
    }

    public function findUnreadNotifications(User $user, Entry $entry)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.entry = :entry')
            ->andWhere('n.status = :status')
            ->setParameters(
                [
                    'user'   => $user,
                    'entry'  => $entry,
                    'status' => Notification::STATUS_NEW,
                ]
            )
            ->getQuery()
            ->getResult();
    }
}
