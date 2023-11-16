<?php

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Message\DeleteImageMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class MagazineDetachIcon
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

        $this->messageBus->dispatch(new DeleteImageMessage($image));
    }
}
