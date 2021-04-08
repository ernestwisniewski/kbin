<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

class EntryFactory
{
    public function createFromDto(EntryDto $entryDto, User $user): Entry
    {
        return new Entry(
            $entryDto->title,
            $entryDto->url,
            $entryDto->body,
            $entryDto->magazine,
            $user,
            $entryDto->isAdult,
        );
    }

    public function createDto(Entry $entry): EntryDto
    {
        return (new EntryDto())->create(
            $entry->magazine,
            $entry->title,
            $entry->url,
            $entry->body,
            null,
            $entry->isAdult,
            $entry->badges,
            $entry->getId()
        );
    }
}
