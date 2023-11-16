<?php

declare(strict_types=1);

namespace App\Kbin\Entry\Badge;

use App\DTO\BadgeDto;
use App\Entity\Badge;
use Doctrine\ORM\EntityManagerInterface;

class EntryBadgeCreate
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(BadgeDto $dto): Badge
    {
        $badge = new Badge($dto->magazine, $dto->name);

        $this->entityManager->persist($badge);
        $this->entityManager->flush();

        return $badge;
    }
}
