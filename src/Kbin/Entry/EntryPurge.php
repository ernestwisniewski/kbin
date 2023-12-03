<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\Entity\Entry;
use App\Entity\User;
use App\Kbin\Entry\EventSubscriber\Event\EntryBeforePurgeEvent;
use App\Kbin\EntryComment\EntryCommentPurge;
use App\Kbin\MessageBus\ImagePurgeMessage;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class EntryPurge
{
    public function __construct(
        private EntryCommentPurge $entryCommentPurge,
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(User $user, Entry $entry): void
    {
        $this->eventDispatcher->dispatch(new EntryBeforePurgeEvent($entry, $user));

        $image = $entry->image?->filePath;

        $sort = new Criteria(null, ['createdAt' => Criteria::DESC]);
        foreach ($entry->comments->matching($sort) as $comment) {
            ($this->entryCommentPurge)($user, $comment);
        }

        $this->entityManager->remove($entry);
        $this->entityManager->flush();

        if ($image) {
            $this->messageBus->dispatch(new ImagePurgeMessage($image));
        }
    }
}
