<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EntryComment;

use App\Entity\EntryComment;
use App\Entity\User;
use App\Event\EntryComment\EntryCommentBeforePurgeEvent;
use App\Event\EntryComment\EntryCommentPurgedEvent;
use App\Kbin\MessageBus\ImagePurgeMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class EntryCommentPurge
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user, EntryComment $comment): void
    {
        $this->eventDispatcher->dispatch(new EntryCommentBeforePurgeEvent($comment, $user));

        $magazine = $comment->entry->magazine;
        $image = $comment->image?->filePath;
        $comment->entry->removeComment($comment);

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        if ($image) {
            $this->messageBus->dispatch(new ImagePurgeMessage($image));
        }

        $this->eventDispatcher->dispatch(new EntryCommentPurgedEvent($magazine));
    }
}
