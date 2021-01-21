<?php declare(strict_types = 1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Factory\EntryFactory;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;

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

        $this->entityManager->persist($entry);

        return $entry;
    }
}
