<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;

class EntryFactory
{
    public function createFromDto(EntryDto $entryDto, User $user): Entry
    {
        return new Entry(
            $entryDto->getTitle(),
            $entryDto->getUrl(),
            $entryDto->getBody(),
            $entryDto->getMagazine(),
            $user,
            $entryDto->isAdult(),
        );
    }

    public function createDto(Entry $entry): EntryDto
    {
        return (new EntryDto())->create(
            $entry->getMagazine(),
            $entry->getTitle(),
            $entry->getUrl(),
            $entry->getBody(),
            null,
            $entry->isAdult(),
            $entry->getId()
        );
    }
}
