<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\MagazineOwnershipRequest;
use App\Entity\ModeratorRequest;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\Report;
use App\Kbin\Entry\EntryPurge;
use App\Kbin\EntryComment\EntryCommentPurge;
use App\Kbin\Post\PostPurge;
use App\Kbin\PostComment\PostCommentPurge;
use App\Message\MagazinePurgeMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class MagazinePurgeHandler
{
    private ?Magazine $magazine;
    private int $batchSize = 5;

    public function __construct(
        private readonly EntryCommentPurge $entryCommentPurge,
        private readonly EntryPurge $entryPurge,
        private readonly PostCommentPurge $postCommentPurge,
        private readonly PostPurge $postPurge,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(MagazinePurgeMessage $message): void
    {
        $this->magazine = $this->entityManager
            ->getRepository(Magazine::class)
            ->find($message->id);

        if (!$this->magazine) {
            throw new UnrecoverableMessageHandlingException('Magazine not found');
        }

        $retry = $this->removeReports()
            || $this->removeEntryComments()
            || $this->removeEntries()
            || $this->removePostComments()
            || $this->removePosts();

        if ($retry) {
            $this->bus->dispatch($message);
        } else {
            $this->removeModeratorRequests();
            $this->removeModeratorOwnershipRequests();

            if ($message->contentOnly) {
                return;
            }

            $this->entityManager->remove($this->magazine);
            $this->entityManager->flush();
        }
    }

    private function removeEntryComments(): bool
    {
        $comments = $this->entityManager
            ->getRepository(EntryComment::class)
            ->findBy(
                [
                    'magazine' => $this->magazine,
                ],
                ['id' => 'DESC'],
                $this->batchSize
            );

        $retry = false;

        foreach ($comments as $comment) {
            $retry = true;
            ($this->entryCommentPurge)($comment->user, $comment);
        }

        return $retry;
    }

    private function removeEntries(): bool
    {
        $entries = $this->entityManager
            ->getRepository(Entry::class)
            ->findBy(
                [
                    'magazine' => $this->magazine,
                ],
                ['id' => 'DESC'],
                $this->batchSize
            );

        $retry = false;

        foreach ($entries as $entry) {
            $retry = true;
            ($this->entryPurge)($entry->user, $entry);
        }

        return $retry;
    }

    private function removePostComments(): bool
    {
        $comments = $this->entityManager
            ->getRepository(PostComment::class)
            ->findBy(
                [
                    'magazine' => $this->magazine,
                ],
                ['id' => 'DESC'],
                $this->batchSize
            );

        $retry = false;
        foreach ($comments as $comment) {
            $retry = true;
            ($this->postCommentPurge)($comment->user, $comment);
        }

        return $retry;
    }

    private function removePosts(): bool
    {
        $posts = $this->entityManager
            ->getRepository(Post::class)
            ->findBy(
                [
                    'magazine' => $this->magazine,
                ],
                ['id' => 'DESC'],
                $this->batchSize
            );

        $retry = false;

        foreach ($posts as $post) {
            $retry = true;
            ($this->postPurge)($post->user, $post);
        }

        return $retry;
    }

    private function removeReports(): bool
    {
        $em = $this->entityManager;
        $query = $em->createQuery(
            'DELETE FROM '.Report::class.' r WHERE r.magazine = :magazineId'
        );
        $query->setParameter('magazineId', $this->magazine->getId());
        $query->execute();

        return false;
    }

    private function removeModeratorRequests(): void
    {
        $em = $this->entityManager;
        $query = $em->createQuery(
            'DELETE FROM '.ModeratorRequest::class.' r WHERE r.magazine = :magazineId'
        );
        $query->setParameter('magazineId', $this->magazine->getId());
        $query->execute();
    }

    private function removeModeratorOwnershipRequests(): void
    {
        $em = $this->entityManager;
        $query = $em->createQuery(
            'DELETE FROM '.MagazineOwnershipRequest::class.' r WHERE r.magazine = :magazineId'
        );
        $query->setParameter('magazineId', $this->magazine->getId());
        $query->execute();
    }
}
