<?php declare(strict_types=1);

namespace App\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EntryRepository;
use App\Event\EntryCreatedEvent;
use App\Event\EntryUpdatedEvent;
use App\Factory\EntryFactory;
use Webmozart\Assert\Assert;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;

class EntryManager
{
    private EntryFactory $entryFactory;
    private EntryRepository $entryRepository;
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntryFactory $entryFactory,
        EntryRepository $entryRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager
    ) {
        $this->entryFactory    = $entryFactory;
        $this->entryRepository = $entryRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager   = $entityManager;
    }

    public function createEntry(EntryDto $entryDto, User $user): Entry
    {
        $entry    = $this->entryFactory->createFromDto($entryDto, $user);
        $magazine = $entry->getMagazine();

        $this->assertType($entry);

        $magazine->addEntry($entry);

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCreatedEvent($entry)));

        return $entry;
    }

    public function editEntry(Entry $entry, EntryDto $entryDto): Entry
    {
        Assert::same($entry->getMagazine()->getId(), $entryDto->getMagazine()->getId());

        $entry->setTitle($entryDto->getTitle());
        $entry->setUrl($entryDto->getUrl());
        $entry->setBody($entryDto->getBody());

        $this->assertType($entry);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryUpdatedEvent($entry)));

        return $entry;
    }

    public function purgeEntry(Entry $entry): void
    {
        $magazine = $entry->getMagazine();

        $magazine->removeEntry($entry);

        $this->entityManager->remove($entry);
        $this->entityManager->flush();
    }

    public function createEntryDto(Entry $entry): EntryDto
    {
        return $this->entryFactory->createDto($entry);
    }

    private function assertType(Entry $entry): void
    {
        if ($entry->getUrl()) {
            Assert::null($entry->getBody());
        } else {
            Assert::null($entry->getUrl());
        }
    }
}
