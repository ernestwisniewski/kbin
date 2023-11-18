<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry\DTO;

use App\Entity\Badge;
use App\Entity\Magazine;
use App\Kbin\Magazine\DTO\MagazineDto;
use App\Validator\Unique;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[Unique(Badge::class, errorPath: 'name', fields: ['magazine', 'name'])]
#[OA\Schema()]
class EntryBadgeDto
{
    public Magazine|MagazineDto|null $magazine = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 20)]
    #[Groups(['create-badge'])]
    #[OA\Property(nullable: false)]
    public ?string $name = null;
    private ?int $id = null;

    public static function create(Magazine $magazine, string $name, int $id = null): self
    {
        $dto = new EntryBadgeDto();
        $dto->id = $id;
        $dto->magazine = $magazine;
        $dto->name = $name;

        return $dto;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
