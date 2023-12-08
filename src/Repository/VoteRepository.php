<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\VotableInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\EntryCommentVote;
use App\Entity\EntryVote;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostCommentVote;
use App\Entity\PostVote;
use Doctrine\ORM\EntityManagerInterface;

class VoteRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function count(\DateTimeImmutable $date = null, bool $withFederated = null): int
    {
        $conn = $this->entityManager->getConnection();
        $sql = "
        (SELECT id, 'entry' AS type FROM entry_vote {$this->where($date, $withFederated)}) 
        UNION 
        (SELECT id, 'entry_comment' AS type FROM entry_comment_vote {$this->where($date, $withFederated)})
        UNION 
        (SELECT id, 'post' AS type FROM post_vote {$this->where($date, $withFederated)})
        UNION 
        (SELECT id, 'post_comment' AS type FROM post_comment_vote {$this->where($date, $withFederated)})
        ";

        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        return $stmt->rowCount();
    }

    private function where(\DateTimeImmutable $date = null, bool $withFederated = null): string
    {
        $dateWhere = $date ? "created_at > '{$date->format('Y-m-d H:i:s')}'" : '';
        $withoutFederationWhere = 'EXISTS (SELECT * FROM public.user WHERE public.user.ap_id IS NULL and public.user.id=user_id)';
        if ($date and !$withFederated) {
            return "WHERE $dateWhere AND $withoutFederationWhere";
        } elseif ($date and true === $withFederated) {
            return "WHERE $dateWhere";
        } elseif (!$date and !$withFederated) {
            return "WHERE $withoutFederationWhere";
        } else {
            return '';
        }
    }

    public function countBySubject(VotableInterface $subject, $choice): int
    {
        return match (true) {
            $subject instanceof Entry => $this->countByEntry($subject, $choice),
            $subject instanceof EntryComment => $this->countByEntryComment($subject, $choice),
            $subject instanceof Post => $this->countByPost($subject, $choice),
            $subject instanceof PostComment => $this->countByPostComment($subject, $choice),
            default => throw new \LogicException(),
        };
    }

    private function countByEntry(Entry $subject, int $choice): int
    {
        return (int) $this->entityManager->createQuery(
            '
        SELECT COUNT(v.id)
        FROM '.EntryVote::class.' v
        WHERE v.entry = :entry
        AND v.choice = :choice
        '
        )
            ->setParameter('entry', $subject)
            ->setParameter('choice', $choice)
            ->getSingleScalarResult();
    }

    private function countByEntryComment(EntryComment $subject, int $choice): int
    {
        return (int) $this->entityManager->createQuery(
            '
        SELECT COUNT(v.id)
        FROM '.EntryCommentVote::class.' v
        WHERE v.comment = :comment
        AND v.choice = :choice
        '
        )
            ->setParameter('comment', $subject)
            ->setParameter('choice', $choice)
            ->getSingleScalarResult();
    }

    private function countByPost(Post $subject, int $choice): int
    {
        return (int) $this->entityManager->createQuery(
            '
        SELECT COUNT(v.id)
        FROM '.PostVote::class.' v
        WHERE v.post = :post
        AND v.choice = :choice
        '
        )
            ->setParameter('post', $subject)
            ->setParameter('choice', $choice)
            ->getSingleScalarResult();
    }

    private function countByPostComment(PostComment $subject, int $choice): int
    {
        return (int) $this->entityManager->createQuery(
            '
        SELECT COUNT(v.id)
        FROM '.PostCommentVote::class.' v
        WHERE v.comment = :comment
        AND v.choice = :choice
        '
        )
            ->setParameter('comment', $subject)
            ->setParameter('choice', $choice)
            ->getSingleScalarResult();
    }
}
