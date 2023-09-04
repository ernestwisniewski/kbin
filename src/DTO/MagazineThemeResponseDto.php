<?php

declare(strict_types=1);

namespace App\DTO;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class MagazineThemeResponseDto implements \JsonSerializable
{
    // Weird. Swagger thinks this is a magazine unless I specify the model in this annotation
    #[OA\Property(ref: new Model(type: MagazineSmallResponseDto::class))]
    public ?MagazineSmallResponseDto $magazine = null;

    public ?string $customCss = null;
    public ?ImageDto $icon = null;

    public static function create(MagazineDto $magazine, string $customCss = null, ImageDto $icon = null): self
    {
        $dto = new MagazineThemeResponseDto();
        $dto->magazine = new MagazineSmallResponseDto($magazine);
        $dto->customCss = $customCss;
        $dto->icon = $icon;

        return $dto;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'magazine' => $this->magazine->jsonSerialize(),
            'customCss' => $this->customCss,
            'icon' => $this->icon?->jsonSerialize(),
        ];
    }
}
