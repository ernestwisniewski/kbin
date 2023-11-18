<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Kbin\MessageBus\ImagePurgeMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class MagazineIconDetach
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine): void
    {
        if (!$magazine->icon) {
            return;
        }

        $image = $magazine->icon->filePath;

        $magazine->icon = null;

        $this->entityManager->persist($magazine);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new ImagePurgeMessage($image));
    }
}
