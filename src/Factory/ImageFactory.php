<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ImageDto;
use App\Entity\Image;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;

class ImageFactory
{
    public function __construct(
        private readonly ImageManager $imageManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createDto(Image $image): ImageDto
    {
        if (!$this->entityManager->contains($image)) {
            $this->entityManager->persist($image);
            $this->entityManager->flush();
        }

        return ImageDto::create(
            $image->getId(),
            $image->filePath,
            $image->width,
            $image->height,
            $image->altText,
            $image->sourceUrl,
            $this->imageManager->getUrl($image),
        );
    }
}
