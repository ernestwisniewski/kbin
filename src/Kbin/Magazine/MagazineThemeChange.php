<?php

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\DTO\MagazineThemeDto;
use App\Entity\Magazine;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class MagazineThemeChange
{
    public function __construct(
        private ImageRepository $imageRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(MagazineThemeDto $dto): Magazine
    {
        $magazine = $dto->magazine;

        if ($dto->icon && $magazine->icon?->getId() !== $dto->icon->id) {
            $magazine->icon = $this->imageRepository->find($dto->icon->id);
        }

        // custom css
        $customCss = $dto->customCss;

        // add custom background to custom CSS if defined
        $background = null;
        if ($dto->backgroundImage) {
            $background = match ($dto->backgroundImage) {
                'shape1' => '/build/images/shape.png',
                'shape2' => '/build/images/shape2.png',
                default => null,
            };

            $background = $background ? "#middle { background: url($background); height: 100%; }" : null;
            if ($background) {
                $customCss = sprintf('%s %s', $customCss, $background);
            }
        }

        $magazine->customCss = $customCss;
        $this->entityManager->persist($magazine);
        $this->entityManager->flush();

        return $magazine;
    }
}
