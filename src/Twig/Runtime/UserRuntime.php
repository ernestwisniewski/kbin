<?php declare(strict_types = 1);

namespace App\Twig\Runtime;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\RuntimeExtensionInterface;

class UserRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private Security $security,
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
}
