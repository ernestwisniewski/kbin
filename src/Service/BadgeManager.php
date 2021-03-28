<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Badge;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
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

    public function assign(Entry $entry, Collection $badges): Entry
    {
        $badges = $entry->getMagazine()->getBadges()->filter(
            static function (Badge $badge) use ($badges) {
                return $badges->contains($badge->getName());
            }
        );

        $entry->addBadges(...$badges);

        return $entry;
    }
}
