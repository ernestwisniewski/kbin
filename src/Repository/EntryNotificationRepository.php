<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\EntryNotification;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findNewEntryUnreadNotification(User $user, Entry $entry): ?EntryNotification
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
            ->getOneOrNullResult();
    }
}
