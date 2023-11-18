<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry\DTO;

use App\DTO\ContentRequestDto;
use App\Entity\Entry;
use App\Service\SettingsManager;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;

#[OA\Schema()]
class EntryRequestDto extends ContentRequestDto
{
    #[Groups([
        Entry::ENTRY_TYPE_ARTICLE,
        Entry::ENTRY_TYPE_LINK,
        Entry::ENTRY_TYPE_IMAGE,
        Entry::ENTRY_TYPE_VIDEO,
    ])]
    #[OA\Property(example: 'Posted from the API!')]
    public ?string $title = null;
    #[Groups([
        Entry::ENTRY_TYPE_LINK,
        Entry::ENTRY_TYPE_VIDEO,
    ])]
    public ?string $url = null;
    #[Groups([
        Entry::ENTRY_TYPE_ARTICLE,
        Entry::ENTRY_TYPE_LINK,
        Entry::ENTRY_TYPE_IMAGE,
        Entry::ENTRY_TYPE_VIDEO,
    ])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: ['cat', 'blep', 'cute'])]
    public ?array $tags = null;

    // TODO: Support badges whenever/however they're implemented
    // #[Groups([
    //     Entry::ENTRY_TYPE_ARTICLE,
    //     Entry::ENTRY_TYPE_LINK,
    //     Entry::ENTRY_TYPE_IMAGE,
    //     Entry::ENTRY_TYPE_VIDEO,
    // ])]
    // #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    // public ?array $badges = null;

    #[Groups([
        Entry::ENTRY_TYPE_ARTICLE,
        Entry::ENTRY_TYPE_LINK,
        Entry::ENTRY_TYPE_IMAGE,
        Entry::ENTRY_TYPE_VIDEO,
    ])]
    #[OA\Property(example: false)]
    public bool $isOc = false;

    /**
     * Merges this EntryRequestDto with an EntryDto, replacing the $dto's fields with non-null
     * fields from the request object.
     *
     * @param EntryDto $dto The data to merge into
     *
     * @return EntryDto The newly merged entry
     */
    public function mergeIntoDto(EntryDto $dto): EntryDto
    {
        $dto->title = $this->title ?? $dto->title;
        $dto->body = $this->body ?? $dto->body;
        $dto->tags = $this->tags ?? $dto->tags;
        // TODO: Support for badges when they're implemented
        // $dto->badges = $this->badges ?? $dto->badges;
        $dto->isAdult = $this->isAdult ?? $dto->isAdult;
        $dto->isOc = $this->isOc ?? $dto->isOc;
        $dto->lang = $this->lang ?? $dto->lang ?? SettingsManager::getValue('KBIN_DEFAULT_LANG');
        $dto->url = $this->url ?? $dto->url;

        return $dto;
    }
}
