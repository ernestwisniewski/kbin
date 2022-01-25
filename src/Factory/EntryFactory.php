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
            $dto->isOc,
            $dto->lang,
            $dto->ip,
        );
    }

    public function createDto(Entry $entry): EntryDto
    {
        $dto = new EntryDto();

        $dto->magazine   = $entry->magazine;
        $dto->user       = $entry->user;
        $dto->image      = $entry->image;
        $dto->domain     = $entry->domain;
        $dto->title      = $entry->title;
        $dto->url        = $entry->url;
        $dto->body       = $entry->body;
        $dto->comments   = $entry->commentCount;
        $dto->uv         = $entry->countUpVotes();
        $dto->dv         = $entry->countDownVotes();
        $dto->isAdult    = $entry->isAdult;
        $dto->isOc       = $entry->isOc;
        $dto->lang       = $entry->lang;
        $dto->badges     = $entry->badges;
        $dto->slug       = $entry->slug;
        $dto->views      = $entry->views;
        $dto->score      = $entry->score;
        $dto->visibility = $entry->visibility;
        $dto->ip         = $entry->ip;
        $dto->tags       = $entry->tags;
        $dto->createdAt  = $entry->createdAt;
        $dto->lastActive = $entry->lastActive;
        $dto->setId($entry->getId());

        return $dto;
    }
}
