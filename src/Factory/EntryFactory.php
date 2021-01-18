<?php declare(strict_types = 1);

namespace App\Factory;

use App\DTO\EntryDto;
use App\Entity\Entry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Magazine;
use App\Entity\User;

class EntryFactory
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createFromDto(EntryDto $entryDto, User $user): Entry
    {
        $entry = new Entry(
            $entryDto->getTitle(),
            $entryDto->getUrl(),
            $entryDto->getBody(),
            $entryDto->getMagazine(),
            $user
        );

        $this->entityManager->persist($entry);

        return $entry;
    }
}
