<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Badge;
use App\Entity\Magazine;
use App\Validator\Unique;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[Unique(Badge::class, errorPath: 'name', fields: ['magazine', 'name'])]
#[OA\Schema()]
class BadgeDto
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
        $dto = new BadgeDto();
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
