<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Badge;
use Doctrine\ORM\EntityManagerInterface;
use App\DTO\BadgeDto;
use Webmozart\Assert\Assert;

class BadgeManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(BadgeDto $dto): Badge
    {
        $badge = new Badge($dto->getMagazine(), $dto->getName());

        $this->entityManager->persist($badge);
        $this->entityManager->flush();

        return $badge;
    }

    public function edit(Badge $badge, BadgeDto $dto): Badge
    {
        Assert::same($badge->getMagazine()->getId(), $badge->getMagazine()->getId());

        $badge->setName($dto->getName());

        $this->entityManager->persist($badge);
        $this->entityManager->flush();

        return $badge;
    }

    public function delete(Badge $badge): void
    {
        $this->purge($badge);
    }

    public function purge(Badge $badge): void
    {
        $this->entityManager->remove($badge);
        $this->entityManager->flush();
    }
}
