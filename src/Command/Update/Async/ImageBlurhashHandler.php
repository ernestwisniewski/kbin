<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command\Update\Async;

use App\Entity\Image;
use App\Kbin\Image\ImagePathGet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ImageBlurhashHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ImagePathGet $imagePathGet
    ) {
    }

    public function __invoke(ImageBlurhashMessage $message): void
    {
        $repo = $this->entityManager->getRepository(Image::class);

        $image = $repo->find($message->id);

        $image->blurhash = $repo->blurhash(($this->imagePathGet)($image));

        $this->entityManager->persist($image);
        $this->entityManager->flush();
    }
}
