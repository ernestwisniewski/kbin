<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\Magazine;
use App\Entity\MagazineSubscription;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method MagazineSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method MagazineSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method MagazineSubscription[]    findAll()
 * @method MagazineSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagazineSubscriptionRepository extends ServiceEntityRepository
{
    const PER_PAGE = 25;

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
            ->andWhere('u.apId IS NULL')
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
            ->andWhere('u.apId IS NULL')
            ->setParameter('magazine', $post->magazine)
            ->setParameter('user', $post->user)
            ->getQuery()
            ->getResult();
    }

    public function findMagazineSubscribers(int $page, Magazine $magazine): PagerfantaInterface
    {
        $query = $this->createQueryBuilder('ms')
            ->addSelect('u')
            ->join('ms.user', 'u')
            ->andWhere('ms.magazine = :magazine')
            ->andWhere('u.apId IS NULL')
            ->setParameter('magazine', $magazine)
            ->getQuery();

        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $query
            )
        );

        try {
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }
}
