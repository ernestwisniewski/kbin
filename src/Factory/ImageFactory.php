<?php declare(strict_types = 1);

namespace App\Factory;

use App\DTO\ImageDto;
use App\Entity\Image;

class ImageFactory
{
    public function createDto(Image $image): ImageDto
    {
        return (new ImageDto())->create(
            $image->filePath,
            $image->width,
            $image->height,
            $image->getId()
        );
    }
}
