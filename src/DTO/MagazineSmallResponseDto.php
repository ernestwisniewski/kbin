<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Magazine;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class MagazineSmallResponseDto implements \JsonSerializable
{
    public ?string $name = null;
    public ?int $magazineId = null;

    public function __construct(MagazineDto|Magazine $dto)
    {
        $this->name = $dto->name;
        $this->magazineId = $dto->getId();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'magazineId' => $this->magazineId,
            'name' => $this->name,
        ];
    }
}
