<?php declare(strict_types=1);

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;
use http\Exception\InvalidArgumentException;

class EntryFactory
{
    public function createFromDto(EntryDto $entryDto, User $user): Entry
    {
        return new Entry(
            $entryDto->getTitle(),
            $entryDto->getUrl(),
            $entryDto->getBody(),
            $entryDto->getMagazine(),
            $user
        );
    }

    public function createDto(Entry $entry): EntryDto
    {
        $entryDto = new EntryDto();

        $entryDto->setMagazine($entry->getMagazine());
        $entryDto->setTitle($entry->getTitle());
        $entryDto->setBody($entry->getBody());
        $entryDto->setUrl($entry->getUrl());

        return $entryDto;
    }
}
