<?php

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\Entity\Entry;
use App\Entity\Magazine;
use App\Event\Entry\EntryEditedEvent;
use App\Repository\EntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class EntryMagazineChange
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntryRepository $entryRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Entry $entry, Magazine $magazine): void
    {
        $this->entityManager->beginTransaction();

        try {
            $oldMagazine = $entry->magazine;
            $entry->magazine = $magazine;

            foreach ($entry->comments as $comment) {
                $comment->magazine = $magazine;
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            return;
        }

        $oldMagazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($oldMagazine);
        $oldMagazine->entryCount = $this->entryRepository->countEntriesByMagazine($oldMagazine);

        $magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($magazine);
        $magazine->entryCount = $this->entryRepository->countEntriesByMagazine($magazine);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryEditedEvent($entry));
    }
}
