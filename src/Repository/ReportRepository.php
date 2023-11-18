<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\EntryCommentReport;
use App\Entity\EntryReport;
use App\Entity\Moderator;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostCommentReport;
use App\Entity\PostReport;
use App\Entity\Report;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 20;

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
            default => throw new \LogicException(),
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
            default => throw new \LogicException(),
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

    public function findAllPaginated(int $page = 1, string $status = Report::STATUS_PENDING): PagerfantaInterface
    {
        $dql = 'SELECT r FROM '.Report::class.' r';

        if (Report::STATUS_ANY !== $status) {
            $dql .= ' WHERE r.status = :status';
        }

        $dql .= " ORDER BY CASE WHEN r.status = 'pending' THEN 1 ELSE 2 END, r.weight DESC, r.createdAt DESC";

        $query = $this->getEntityManager()->createQuery($dql);

        if (Report::STATUS_ANY !== $status) {
            $query->setParameter('status', $status);
        }

        $pagerfanta = new Pagerfanta(
            new QueryAdapter($query)
        );

        try {
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    public function findByUserPaginated(
        User $user,
        int $page = 1,
        string $status = Report::STATUS_PENDING
    ): PagerfantaInterface {
        $dql = 'SELECT r FROM App\Entity\Report r';

        $dql .= ' WHERE EXISTS (SELECT 1 FROM '.Moderator::class.' m WHERE m.magazine = r.magazine AND m.user = :user)';

        if (Report::STATUS_ANY !== $status) {
            $dql .= ' AND r.status = :status';
        }

        $dql .= " ORDER BY CASE WHEN r.status = 'pending' THEN 1 ELSE 2 END, r.weight DESC, r.createdAt DESC";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('user', $user);

        if (Report::STATUS_ANY !== $status) {
            $query->setParameter('status', $status);
        }

        $pagerfanta = new Pagerfanta(
            new QueryAdapter($query)
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
