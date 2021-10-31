<?php declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function in_array;

class UserVoter extends Voter
{
    const FOLLOW = 'follow';
    const BLOCK = 'block';
    const EDIT_PROFILE = 'edit_profile';
    const MESSAGE = 'message';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof User && in_array($attribute, [self::FOLLOW, self::BLOCK, self::MESSAGE, self::EDIT_PROFILE], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::FOLLOW => $this->canFollow($subject, $user),
            self::BLOCK => $this->canBlock($subject, $user),
            self::MESSAGE => $this->canMessage($subject, $user),
            self::EDIT_PROFILE => $this->canEditProfile($subject, $user),
            default => throw new LogicException(),
        };
    }

    private function canFollow(User $following, User $follower): bool
    {
        if ($following === $follower) {
            return false;
        }

        return true;
    }

    private function canBlock(User $blocked, User $blocker): bool
    {
        if ($blocked === $blocker) {
            return false;
        }

        return true;
    }

    private function canMessage(User $receiver, User $sender): bool
    {
        if (!$sender instanceof User) {
            return false;
        }

        if ($receiver->isBlocked($sender) || $sender->isBlocked($receiver)) {
            return false;
        }

        return true;
    }


    private function canEditProfile(User $subject, User $user): bool
    {
        return $subject === $user;
    }
}
