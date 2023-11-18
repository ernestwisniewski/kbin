<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class MagazineRequestDto
{
    public ?string $name = null;
    public ?string $title = null;
    public ?string $description = null;
    public ?string $rules = null;
    public ?bool $isAdult = null;

    public function mergeIntoDto(MagazineDto $dto): MagazineDto
    {
        $dto->name = $this->name ?? $dto->name;
        $dto->title = $this->title ?? $dto->title;
        $dto->description = $this->description ?? $dto->description;
        $dto->rules = $this->rules ?? $dto->rules;
        $dto->isAdult = null !== $this->isAdult ? $this->isAdult : $dto->isAdult;

        return $dto;
    }
}
