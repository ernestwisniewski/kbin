<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post\DTO;

use App\DTO\ContentRequestDto;
use App\Service\SettingsManager;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class PostRequestDto extends ContentRequestDto
{
    public function mergeIntoDto(PostDto $dto): PostDto
    {
        $dto->body = $this->body ?? $dto->body;
        $dto->lang = $this->lang ?? $dto->lang ?? SettingsManager::getValue('KBIN_DEFAULT_LANG');
        $dto->isAdult = $this->isAdult ?? $dto->isAdult;

        return $dto;
    }
}
