<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine\DTO;

use App\DTO\ImageDto;
use App\Kbin\Entry\DTO\EntryBadgeResponseDto;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class MagazineResponseDto implements \JsonSerializable
{
    public ?MagazineModeratorResponseDto $owner = null;
    public ?ImageDto $icon = null;
    public ?string $name = null;
    public ?string $title = null;
    public ?string $description = null;
    public ?string $rules = null;
    public int $subscriptionsCount = 0;
    public int $entryCount = 0;
    public int $entryCommentCount = 0;
    public int $postCount = 0;
    public int $postCommentCount = 0;
    public bool $isAdult = false;
    public ?bool $isUserSubscribed = null;
    public ?bool $isBlockedByUser = null;
    #[OA\Property(type: 'array', description: 'Magazine tags', items: new OA\Items(type: 'string'))]
    public ?array $tags = null;
    #[OA\Property(type: 'array', description: 'Magazine badges', items: new OA\Items(ref: new Model(type: EntryBadgeResponseDto::class)))]
    public ?array $badges = null;
    #[OA\Property(type: 'array', description: 'Moderator list', items: new OA\Items(ref: new Model(type: MagazineModeratorResponseDto::class)))]
    public ?array $moderators = null;
    public ?string $apId = null;
    public ?string $apProfileId = null;
    public ?int $magazineId = null;

    public static function create(
        MagazineModeratorResponseDto $owner = null,
        ImageDto $icon = null,
        string $name = null,
        string $title = null,
        string $description = null,
        string $rules = null,
        int $subscriptionsCount = 0,
        int $entryCount = 0,
        int $entryCommentCount = 0,
        int $postCount = 0,
        int $postCommentCount = 0,
        bool $isAdult = false,
        bool $isUserSubscribed = null,
        bool $isBlockedByUser = null,
        array $tags = null,
        array $badges = null,
        array $moderators = null,
        string $apId = null,
        string $apProfileId = null,
        int $magazineId = null,
    ): self {
        $dto = new MagazineResponseDto();
        $dto->owner = $owner;
        $dto->icon = $icon;
        $dto->name = $name;
        $dto->title = $title;
        $dto->description = $description;
        $dto->rules = $rules;
        $dto->subscriptionsCount = $subscriptionsCount;
        $dto->entryCount = $entryCount;
        $dto->entryCommentCount = $entryCommentCount;
        $dto->postCount = $postCount;
        $dto->postCommentCount = $postCommentCount;
        $dto->isAdult = $isAdult;
        $dto->isUserSubscribed = $isUserSubscribed;
        $dto->isBlockedByUser = $isBlockedByUser;
        $dto->tags = $tags;
        $dto->badges = $badges;
        $dto->moderators = $moderators;
        $dto->apId = $apId;
        $dto->apProfileId = $apProfileId;
        $dto->magazineId = $magazineId;

        return $dto;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'magazineId' => $this->magazineId,
            'owner' => $this->owner->jsonSerialize(),
            'icon' => $this->icon ? $this->icon->jsonSerialize() : null,
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'rules' => $this->rules,
            'subscriptionsCount' => $this->subscriptionsCount,
            'entryCount' => $this->entryCount,
            'entryCommentCount' => $this->entryCommentCount,
            'postCount' => $this->postCount,
            'postCommentCount' => $this->postCommentCount,
            'isAdult' => $this->isAdult,
            'isUserSubscribed' => $this->isUserSubscribed,
            'isBlockedByUser' => $this->isBlockedByUser,
            'tags' => $this->tags,
            'badges' => array_map(fn (EntryBadgeResponseDto $badge) => $badge->jsonSerialize(), $this->badges),
            'moderators' => array_map(fn (MagazineModeratorResponseDto $moderator) => $moderator->jsonSerialize(), $this->moderators),
            'apId' => $this->apId,
            'apProfileId' => $this->apProfileId,
        ];
    }
}
