<?php declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\PostNotification;

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
}
