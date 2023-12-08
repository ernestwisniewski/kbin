<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\EntryCommentFavourite;
use App\Entity\EntryFavourite;
use App\Entity\Favourite;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostCommentFavourite;
use App\Entity\PostFavourite;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Favourite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Favourite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Favourite[]    findAll()
 * @method Favourite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FavouriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favourite::class);
    }

    public function countBySubject(FavouriteInterface $subject): int
    {
        return match (true) {
            $subject instanceof Entry => $this->countByEntry($subject),
            $subject instanceof EntryComment => $this->countByEntryComment($subject),
            $subject instanceof Post => $this->countByPost($subject),
            $subject instanceof PostComment => $this->countByPostComment($subject),
            default => throw new \LogicException(),
        };
    }

    public function findBySubject(User $user, FavouriteInterface $subject): ?Favourite
    {
        return match (true) {
            $subject instanceof Entry => $this->findByEntry($user, $subject),
            $subject instanceof EntryComment => $this->findByEntryComment($user, $subject),
            $subject instanceof Post => $this->findByPost($user, $subject),
            $subject instanceof PostComment => $this->findByPostComment($user, $subject),
            default => throw new \LogicException(),
        };
    }

    private function findByEntry(User $user, Entry $entry): ?EntryFavourite
    {
        $dql = 'SELECT f FROM '.EntryFavourite::class.' f WHERE f.entry = :entry AND f.user = :user';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameters(['entry' => $entry, 'user' => $user])
            ->getOneOrNullResult();
    }

    private function findByEntryComment(User $user, EntryComment $comment): ?EntryCommentFavourite
    {
        $dql = 'SELECT f FROM '.EntryCommentFavourite::class.' f WHERE f.entryComment = :comment AND f.user = :user';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameters(['comment' => $comment, 'user' => $user])
            ->getOneOrNullResult();
    }

    private function findByPost(User $user, Post $post): ?PostFavourite
    {
        $dql = 'SELECT f FROM '.PostFavourite::class.' f WHERE f.post = :post AND f.user = :user';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameters(['post' => $post, 'user' => $user])
            ->getOneOrNullResult();
    }

    private function findByPostComment(User $user, PostComment $comment): ?PostCommentFavourite
    {
        $dql = 'SELECT f FROM '.PostCommentFavourite::class.' f WHERE f.postComment = :comment AND f.user = :user';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameters(['comment' => $comment, 'user' => $user])
            ->getOneOrNullResult();
    }

    private function countByEntry(Entry $subject): int
    {
        return (int) $this->_em->createQuery(
            '
        SELECT COUNT(f.id)
        FROM '.EntryFavourite::class.' f
        WHERE f.entry = :entry
        '
        )
            ->setParameter('entry', $subject)
            ->getSingleScalarResult();
    }

    private function countByEntryComment(EntryComment $subject): int
    {
        return (int) $this->_em->createQuery(
            '
        SELECT COUNT(f.id)
        FROM '.EntryCommentFavourite::class.' f
        WHERE f.entryComment = :comment
        '
        )
            ->setParameter('comment', $subject)
            ->getSingleScalarResult();
    }

    private function countByPost(Post $subject): int
    {
        return (int) $this->_em->createQuery(
            '
        SELECT COUNT(f.id)
        FROM '.PostFavourite::class.' f
        WHERE f.post = :post
        '
        )
            ->setParameter('post', $subject)
            ->getSingleScalarResult();
    }

    private function countByPostComment(PostComment $subject): int
    {
        return (int) $this->_em->createQuery(
            '
        SELECT COUNT(f.id)
        FROM '.PostCommentFavourite::class.' f
        WHERE f.postComment = :comment
        '
        )
            ->setParameter('comment', $subject)
            ->getSingleScalarResult();
    }
}
