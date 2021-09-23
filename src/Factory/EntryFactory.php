<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;

class EntryFactory
{
    public function createFromDto(EntryDto $dto, User $user): Entry
    {
        return new Entry(
            $dto->title,
            $dto->url,
            $dto->body,
            $dto->magazine,
            $user,
            $dto->isAdult,
            $dto->ip,
        );
    }

    public function createDto(Entry $entry): EntryDto
    {
        return (new EntryDto())->create(
            $entry->magazine,
            $entry->user,
            $entry->title,
            $entry->url,
            $entry->body,
            $entry->commentCount,
            $entry->countUpVotes(),
            $entry->countDownVotes(),
            $entry->domain,
            $entry->image,
            $entry->isAdult,
            $entry->badges,
            $entry->slug,
            $entry->score,
            $entry->views,
            $entry->ip,
            $entry->createdAt,
            $entry->getId()
        );
    }
}
