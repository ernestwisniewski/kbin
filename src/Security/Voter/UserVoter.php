<?php declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Magazine;
use App\Entity\User;

class UserVoter extends Voter
{
    const FOLLOW = 'follow';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof User && \in_array($attribute, [self::FOLLOW, true]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::FOLLOW:
                return $this->canFollow($subject, $user);
        }

        throw new \LogicException();
    }

    private function canFollow(User $following, User $follower): bool
    {
        if ($following === $follower) {
            return false;
        }

        return true;
    }
}
