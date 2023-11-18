<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EntryComment\DTO;

use App\DTO\ContentRequestDto;
use App\Service\SettingsManager;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class EntryCommentRequestDto extends ContentRequestDto
{
    /**
     * Merges this EntryCommentRequestDto with an EntryCommentDto, replacing the $dto's fields with non-null
     * fields from the request schema object.
     *
     * @param EntryCommentDto $dto The data to merge into
     *
     * @return EntryCommentDto The newly merged entry
     */
    public function mergeIntoDto(EntryCommentDto $dto): EntryCommentDto
    {
        $dto->body = $this->body ?? $dto->body;
        $dto->lang = $this->lang ?? $dto->lang ?? SettingsManager::getValue('KBIN_DEFAULT_LANG');
        $dto->isAdult = $this->isAdult ?? $dto->isAdult;

        return $dto;
    }
}
