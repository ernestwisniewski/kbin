<?php

namespace App\Twig\Runtime;

use App\Entity\User;
use App\Repository\ReputationRepository;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\RuntimeExtensionInterface;

class UserExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly Security $security, private ReputationRepository $reputationRepository)
    {
    }

    public function isFollowed(User $followed)
    {
        if (!$this->security->getUser()) {
            return false;
        }

        return $this->security->getUser()->isFollower($followed);
    }

    public function isBlocked(User $blocked)
    {
        if (!$this->security->getUser()) {
            return false;
        }

        return $this->security->getUser()->isBlocked($blocked);
    }

    public function username(string $value, ?bool $withApPostfix = false): string
    {
        $value = ltrim($value, '@');

        if (true === $withApPostfix) {
            return $value;
        }

        return explode('@', $value)[0];
    }

    public function getReputationTotal(User $user): int
    {
        return $this->reputationRepository->getUserReputationTotal($user);
    }
}
