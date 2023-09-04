<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Image;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

#[OA\Schema()]
class MagazineThemeRequestDto extends ImageUploadDto
{
    #[Groups(['common'])]
    public ?string $customCss = null;
    // Currently not used
    // #[Groups(['common'])]
    // public ?string $customJs = null;
    // #[Groups(['common'])]
    // public ?string $primaryColor = null;
    // #[Groups(['common'])]
    // public ?string $primaryDarkerColor = null;
    #[Groups(['common'])]
    #[OA\Property(enum: ['shape1', 'shape2'])]
    public ?string $backgroundImage = null;
    #[Ignore]
    public ?Image $icon = null;

    public function mergeIntoDto(MagazineThemeDto $dto): MagazineThemeDto
    {
        $dto->customCss = $this->customCss ?? $dto->customCss;
        $dto->backgroundImage = $this->backgroundImage ?? $dto->backgroundImage;
        $dto->icon = $this->icon ?? $dto->icon;

        return $dto;
    }
}
