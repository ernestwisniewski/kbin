<?php

declare(strict_types=1);

namespace App\Kbin\Entry\Badge;

use App\Entity\Badge;
use App\Kbin\Entry\DTO\EntryBadgeDto;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

class EntryBadgeEdit
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Badge $badge, EntryBadgeDto $dto): Badge
    {
        Assert::same($badge->magazine->getId(), $badge->magazine->getId());

        $badge->name = $dto->name;

        $this->entityManager->persist($badge);
        $this->entityManager->flush();

        return $badge;
    }
}
