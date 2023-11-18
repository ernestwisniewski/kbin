<?php

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Repository\ReputationRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class UserReputationGet
{
    public function __construct(
        private ReputationRepository $reputationRepository,
        private CacheInterface $cache
    ) {
    }

    public function __invoke(User $user): int
    {
        return $this->cache->get(
            "user_reputation_{$user->getId()}",
            function (ItemInterface $item) use ($user) {
                $item->expiresAfter(60);

                return $this->reputationRepository->getUserReputationTotal($user);
            }
        );
    }
}
