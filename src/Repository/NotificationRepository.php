<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\PostComment;
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
    public const PER_PAGE = 25;
    public const STATUS_ALL = 'all';
    public const STATUS_OPTIONS = [
        self::STATUS_ALL,
        Notification::STATUS_NEW,
        Notification::STATUS_READ,
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findByUser(
        User $user,
        ?int $page,
        string $status = self::STATUS_ALL,
        int $perPage = self::PER_PAGE
    ): PagerfantaInterface {
        $qb = $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.id', 'DESC');

        if (self::STATUS_ALL !== $status) {
            $qb->andWhere('n.status = :status')
                ->setParameter('status', $status);
        }

        $pagerfanta = new Pagerfanta(
            new QueryAdapter(
                $qb
            )
        );

        try {
            $pagerfanta->setMaxPerPage($perPage);
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
            fn ($notification) => (isset($notification->entry) && $notification->entry === $entry)
                || (isset($notification->entryComment) && $notification->entryComment->entry === $entry)
        );
    }

    public function findUnreadNotifications(User $user): array
    {
        $dql = 'SELECT n FROM '.Notification::class.' n WHERE n.user = :user AND n.status = :status';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('user', $user)
            ->setParameter('status', Notification::STATUS_NEW)
            ->getResult();
    }

    public function countUnreadNotifications(User $user): int
    {
        $dql = 'SELECT count(n.id) FROM '.Notification::class.' n WHERE n.user = :user AND n.status = :status';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('user', $user)
            ->setParameter('status', Notification::STATUS_NEW)
            ->getSingleScalarResult();
    }

    public function findUnreadPostNotifications(User $user, Post $post): iterable
    {
        $result = $this->findUnreadNotifications($user);

        return array_filter(
            $result,
            fn ($notification) => (isset($notification->post) && $notification->post === $post)
                || (isset($notification->postComment) && $notification->postComment->post === $post)
        );
    }

    public function removeEntryNotifications(Entry $entry): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'DELETE FROM notification AS n WHERE n.entry_id = :entryId';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('entryId', $entry->getId());

        $stmt->executeQuery();
    }

    public function removeEntryCommentNotifications(EntryComment $comment): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'DELETE FROM notification AS n WHERE n.entry_comment_id = :commentId';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('commentId', $comment->getId());

        $stmt->executeQuery();
    }

    public function removePostNotifications(Post $post): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'DELETE FROM notification AS n WHERE n.post_id = :postId';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('postId', $post->getId());

        $stmt->executeQuery();
    }

    public function removePostCommentNotifications(PostComment $comment): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'DELETE FROM notification AS n WHERE n.post_comment_id = :commentId';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('commentId', $comment->getId());

        $stmt->executeQuery();
    }
}
