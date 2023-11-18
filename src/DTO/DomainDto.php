<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class DomainDto implements \JsonSerializable
{
    public ?string $name = null;
    public ?int $entryCount = null;
    public ?int $subscriptionsCount = null;
    public ?bool $isUserSubscribed = null;
    public ?bool $isBlockedByUser = null;
    #[OA\Property('domainId')]
    private ?int $id;

    public static function create(string $name, ?int $entryCount, ?int $subscriptionsCount, int $id = null): self
    {
        $toReturn = new DomainDto();
        $toReturn->id = $id;
        $toReturn->name = $name;
        $toReturn->entryCount = $entryCount;
        $toReturn->subscriptionsCount = $subscriptionsCount;

        return $toReturn;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'domainId' => $this->getId(),
            'name' => $this->name,
            'entryCount' => $this->entryCount,
            'subscriptionsCount' => $this->subscriptionsCount,
            'isUserSubscribed' => $this->isUserSubscribed,
            'isBlockedByUser' => $this->isBlockedByUser,
        ];
    }
}
