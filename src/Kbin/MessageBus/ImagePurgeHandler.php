<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\MessageBus;

use App\Repository\ImageRepository;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ImagePurgeHandler
{
    public function __construct(
        private ImageRepository $imageRepository,
        private ImageManager $imageManager,
        private EntityManagerInterface $entityManager,
        private ManagerRegistry $managerRegistry
    ) {
    }

    public function __invoke(ImagePurgeMessage $message): void
    {
        $image = $this->imageRepository->findOneBy(['filePath' => $message->path]);

        if ($image) {
            $this->entityManager->beginTransaction();

            try {
                $this->entityManager->remove($image);
                $this->entityManager->flush();

                $this->entityManager->commit();
            } catch (\Exception $e) {
                $this->entityManager->rollback();
                $this->managerRegistry->resetManager();

                return;
            }
        }

        $this->imageManager->remove($message->path);
    }
}
