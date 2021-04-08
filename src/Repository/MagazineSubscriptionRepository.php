<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\MagazineSubscription;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Moderator;

/**
 * @method Moderator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Moderator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Moderator[]    findAll()
 * @method Moderator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MagazineSubscription::class);
    }

    public function findNewEntrySubscribers(Entry $entry): array
    {
        return $this->createQueryBuilder('ms')
            ->addSelect('u')
            ->join('ms.user', 'u')
            ->where('u.notifyOnNewEntry = true')
            ->andWhere('ms.magazine = :magazine')
            ->andWhere('u != :user')
            ->setParameter('magazine', $entry->magazine)
            ->setParameter('user', $entry->user)
            ->getQuery()
            ->getResult();
    }

    public function findNewPostSubscribers(Post $post)
    {
        return $this->createQueryBuilder('ms')
            ->addSelect('u')
            ->join('ms.user', 'u')
            ->where('u.notifyOnNewPost = true')
            ->andWhere('ms.magazine = :magazine')
            ->andWhere('u != :user')
            ->setParameter('magazine', $post->getMagazine())
            ->setParameter('user', $post->getUser())
            ->getQuery()
            ->getResult();
    }
}
