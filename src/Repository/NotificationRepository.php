<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    const PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findByUser(User $user, ?int $page, bool $onlyNew = false): PagerfantaInterface
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.id', 'DESC');

        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $qb
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

    public function findUnreadEntryNotifications(User $user, Entry $entry): iterable
    {
        $result = $this->findUnreadNotifications($user);

        return array_filter(
            $result,
            fn($notification) => (isset($notification->entry) && $notification->entry === $entry)
                || (isset($notification->entryComment) && $notification->entryComment->entry === $entry)
        );
    }

    private function findUnreadNotifications(User $user): array
    {
        $dql = 'SELECT n FROM '.Notification::class.' n WHERE n.user = :user AND n.status = :status';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('user', $user)
            ->setParameter('status', Notification::STATUS_NEW)
            ->getResult();
    }

    public function findUnreadPostNotifications(User $user, Post $post): iterable
    {
        $result = $this->findUnreadNotifications($user);

        return array_filter(
            $result,
            fn($notification) => (isset($notification->post) && $notification->post === $post)
                || (isset($notification->postComment) && $notification->postComment->post === $post)
        );
    }
}
