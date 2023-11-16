<?php

declare(strict_types=1);

namespace App\Kbin\Magazine\Moderator;

use App\DTO\ModeratorDto;
use App\Entity\Moderator;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;

readonly class MagazineModeratorAdd
{
    public function __construct(
        private CacheInterface $cache,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(ModeratorDto $dto, ?bool $isOwner = false): void
    {
        $magazine = $dto->magazine;

        $magazine->addModerator(new Moderator($magazine, $dto->user, $isOwner, true));

        $this->entityManager->flush();

        $this->clearCommentsCache($dto->user);
    }

    private function clearCommentsCache(User $user): void
    {
        $this->cache->invalidateTags([
            'post_comments_user_'.$user->getId(),
            'entry_comments_user_'.$user->getId(),
        ]); // @todo move to event subscriber
    }
}
