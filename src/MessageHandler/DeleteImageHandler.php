<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\DeleteImageMessage;
use App\Repository\ImageRepository;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteImageHandler
{
    public function __construct(
        private readonly ImageRepository $imageRepository,
        private readonly ImageManager $imageManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly ManagerRegistry $managerRegistry
    ) {
    }

    public function __invoke(DeleteImageMessage $message)
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
