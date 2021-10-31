<?php declare(strict_types = 1);

namespace App\Service;

use App\DTO\BadgeDto;
use App\Entity\Badge;
use App\Entity\Entry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

class BadgeManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function create(BadgeDto $dto): Badge
    {
        $badge = new Badge($dto->magazine, $dto->name);

        $this->entityManager->persist($badge);
        $this->entityManager->flush();

        return $badge;
    }

    public function edit(Badge $badge, BadgeDto $dto): Badge
    {
        Assert::same($badge->magazine->getId(), $badge->magazine->getId());

        $badge->name = $dto->name;

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

    public function assign(Entry $entry, Collection $badges): Entry
    {
        $badges = $entry->magazine->badges->filter(
            static function (Badge $badge) use ($badges) {
                return $badges->contains($badge->name);
            }
        );

        $entry->setBadges(...$badges);

        return $entry;
    }
}
