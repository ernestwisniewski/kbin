<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

#[OA\Schema()]
class ImageDto implements \JsonSerializable
{
    #[Groups(['common'])]
    public ?string $filePath = null;
    #[Groups(['common'])]
    public ?string $sourceUrl = null;
    #[Groups(['common'])]
    public ?string $storageUrl = null;
    #[Groups(['common'])]
    public ?string $altText = null;
    #[Groups(['common'])]
    public ?int $width = null;
    #[Groups(['common'])]
    public ?int $height = null;
    #[Ignore]
    public ?int $id = null;

    public static function create(int $id, string $filePath, int $width = null, int $height = null, string $altText = null, string $sourceUrl = null, string $storageUrl = null): self
    {
        $dto = new ImageDto();
        $dto->filePath = $filePath;
        $dto->altText = $altText;
        $dto->width = $width;
        $dto->height = $height;
        $dto->sourceUrl = $sourceUrl;
        $dto->storageUrl = $storageUrl;
        $dto->id = $id;

        return $dto;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'filePath' => $this->filePath,
            'sourceUrl' => $this->sourceUrl,
            'storageUrl' => $this->storageUrl,
            'altText' => $this->altText,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
