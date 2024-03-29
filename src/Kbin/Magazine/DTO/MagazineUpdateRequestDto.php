<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine\DTO;

use App\Repository\ImageRepository;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class MagazineUpdateRequestDto
{
    public ?int $iconId = null;
    public ?string $title = null;
    public ?string $description = null;
    public ?string $rules = null;
    public ?bool $isAdult = null;

    public function mergeIntoDto(MagazineDto $dto, ImageRepository $imageRepository): MagazineDto
    {
        $dto->icon = null !== $this->iconId ? $imageRepository->find($this->iconId) : $dto->icon;
        $dto->title = $this->title ?? $dto->title;
        $dto->description = $this->description ?? $dto->description;
        $dto->rules = $this->rules ?? $dto->rules;
        $dto->isAdult = null === $this->isAdult ? $this->isAdult : $dto->isAdult;

        return $dto;
    }
}
