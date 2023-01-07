<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Entity\User;
use App\Repository\ReputationRepository;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\RuntimeExtensionInterface;

class UserRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly ReputationRepository $reputationRepository
    ) {
    }

    public function isUserFollow(User $following): bool
    {
        if (!$user = $this->security->getUser()) {
            return false;
        }

        return $this->security->getUser()->isFollower($following);
    }

    public function isUserBlocked(User $blocked): bool
    {
        if (!$user = $this->security->getUser()) {
            return false;
        }

        return $this->security->getUser()->isBlocked($blocked);
    }

    public function getReputationTotal(User $user): int
    {
        return $this->reputationRepository->getUserReputationTotal($user);
    }
}
