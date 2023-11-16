<?php

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\Entity\Entry;
use App\Event\Entry\EntryEditedEvent;
use App\Message\DeleteImageMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class EntryDetachImage
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Entry $entry): void
    {
        $image = $entry->image->filePath;

        $entry->image = null;

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new DeleteImageMessage($image));

        $this->eventDispatcher->dispatch(new EntryEditedEvent($entry));
    }
}
