<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\PostNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostNotification[]    findAll()
 * @method PostNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostNotification::class);
    }

    public function findNewEntryUnreadNotification(User $user, Post $post): ?PostNotification
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
