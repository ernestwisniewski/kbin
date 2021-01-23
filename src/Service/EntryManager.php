<?php declare(strict_types = 1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Factory\EntryFactory;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;
use Webmozart\Assert\Assert;

class EntryManager
{
    /**
     * @var EntryFactory
     */
    private $entryFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntryFactory $entryFactory, EntityManagerInterface $entityManager)
    {

        $this->entryFactory  = $entryFactory;
        $this->entityManager = $entityManager;
    }

    public function createEntry(EntryDto $entryDto, User $user): Entry
    {
        $entry = $this->entryFactory->createFromDto($entryDto, $user);

        $this->assertType($entry);

        $this->entityManager->persist($entry);

        return $entry;
    }

    public function editEntry(Entry $entry, EntryDto $entryDto): Entry
    {
        $entry->setTitle($entryDto->getTitle());
        $entry->setUrl($entryDto->getUrl());
        $entry->setBody($entryDto->getBody());

        $this->assertType($entry);

        return $entry;
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
