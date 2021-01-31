<?php declare(strict_types=1);

namespace App\Service;

use App\Message\EntryCreatedMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EntryRepository;
use App\Event\EntryCreatedEvent;
use App\Event\EntryUpdatedEvent;
use App\Event\EntryPurgedEvent;
use App\Factory\EntryFactory;
use Webmozart\Assert\Assert;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Utils\Embed;
use App\Entity\User;

class EntryManager
{
    private EntryFactory $entryFactory;
    private EntryRepository $entryRepository;
    private EventDispatcherInterface $eventDispatcher;
    private MessageBusInterface $messageBus;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntryFactory $entryFactory,
        EntryRepository $entryRepository,
        EventDispatcherInterface $eventDispatcher,
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager
    ) {
        $this->entryFactory    = $entryFactory;
        $this->entryRepository = $entryRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageBus      = $messageBus;
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

        $this->eventDispatcher->dispatch(new EntryCreatedEvent($entry));
        $this->messageBus->dispatch(new EntryCreatedMessage($entry->getId()));

        return $entry;
    }

    public function editEntry(Entry $entry, EntryDto $entryDto): Entry
    {
        Assert::same($entry->getMagazine()->getId(), $entryDto->getMagazine()->getId());

        $entry->setTitle($entryDto->getTitle());
        $entry->setUrl($entryDto->getUrl());
        $entry->setBody($entryDto->getBody());
        $entry->setImage($entryDto->getImage());

        $this->assertType($entry);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryUpdatedEvent($entry)));

        return $entry;
    }

    public function purgeEntry(Entry $entry): void
    {
        $entry->getMagazine()->removeEntry($entry);

        $this->eventDispatcher->dispatch((new EntryPurgedEvent($entry)));

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
