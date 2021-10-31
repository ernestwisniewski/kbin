<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\EntryCommentReport;
use App\Entity\EntryReport;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostCommentReport;
use App\Entity\PostReport;
use App\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

/**
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    public function findBySubject(ReportInterface $subject): ?Report
    {
        return match (true) {
            $subject instanceof Entry => $this->findByEntry($subject),
            $subject instanceof EntryComment => $this->findByEntryComment($subject),
            $subject instanceof Post => $this->findByPost($subject),
            $subject instanceof PostComment => $this->findByPostComment($subject),
            default => throw new LogicException(),
        };
    }

    private function findByEntry(Entry $entry): ?EntryReport
    {
        $dql = 'SELECT r FROM '.EntryReport::class.' r WHERE r.entry = :entry';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('entry', $entry)
            ->getOneOrNullResult();
    }

    private function findByEntryComment(EntryComment $comment): ?EntryCommentReport
    {
        $dql = 'SELECT r FROM '.EntryCommentReport::class.' r WHERE r.entryComment = :comment';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('comment', $comment)
            ->getOneOrNullResult();
    }

    private function findByPost(Post $post): ?PostReport
    {
        $dql = 'SELECT r FROM '.PostReport::class.' r WHERE r.post = :post';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('post', $post)
            ->getOneOrNullResult();
    }

    private function findByPostComment(PostComment $comment): ?PostCommentReport
    {
        $dql = 'SELECT r FROM '.PostCommentReport::class.' r WHERE r.postComment = :comment';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('comment', $comment)
            ->getOneOrNullResult();
    }

    public function findPendingBySubject(ReportInterface $subject): ?Report
    {
        return match (true) {
            $subject instanceof Entry => $this->findPendingByEntry($subject),
            $subject instanceof EntryComment => $this->findPendingByEntryComment($subject),
            $subject instanceof Post => $this->findPendingByPost($subject),
            $subject instanceof PostComment => $this->findPendingByPostComment($subject),
            default => throw new LogicException(),
        };
    }

    private function findPendingByEntry(Entry $entry): ?EntryReport
    {
        $dql = 'SELECT r FROM '.EntryReport::class.' r WHERE r.entry = :entry AND r.status = :status';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('entry', $entry)
            ->setParameter('status', Report::STATUS_PENDING)
            ->getOneOrNullResult();
    }

    private function findPendingByEntryComment(EntryComment $comment): ?EntryCommentReport
    {
        $dql = 'SELECT r FROM '.EntryCommentReport::class.' r WHERE r.entryComment = :comment AND r.status = :status';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('comment', $comment)
            ->setParameter('status', Report::STATUS_PENDING)
            ->getOneOrNullResult();
    }

    private function findPendingByPost(Post $post): ?PostReport
    {
        $dql = 'SELECT r FROM '.PostReport::class.' r WHERE r.post = :post AND r.status = :status';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('post', $post)
            ->setParameter('status', Report::STATUS_PENDING)
            ->getOneOrNullResult();
    }

    private function findPendingByPostComment(PostComment $comment): ?PostCommentReport
    {
        $dql = 'SELECT r FROM '.PostCommentReport::class.' r WHERE r.postComment = :comment AND r.status = :status';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('comment', $comment)
            ->setParameter('status', Report::STATUS_PENDING)
            ->getOneOrNullResult();
    }
}
