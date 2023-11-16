<?php

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\Entity\Entry;
use App\Event\Entry\EntryEditedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class EntryChangeLang
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Entry $entry, string $lang = 'en'): void
    {
        $entry->lang = $lang;

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryEditedEvent($entry));
    }
}
