<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\PostCreatedNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostCreatedNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostCreatedNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostCreatedNotification[]    findAll()
 * @method PostCreatedNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostCreatedNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostCreatedNotification::class);
    }

    public function findNewEntryUnreadNotification(User $user, Post $post): ?PostCreatedNotification
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.post = :post')
            ->andWhere('n.status = :status')
            ->setParameters(
                [
                    'user'   => $user,
                    'post'   => $post,
                    'status' => Notification::STATUS_NEW,
                ]
            )
            ->getQuery()
            ->getOneOrNullResult();
    }
}
