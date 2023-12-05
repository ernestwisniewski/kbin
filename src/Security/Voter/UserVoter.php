<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const FOLLOW = 'follow';
    public const BLOCK = 'block';
    public const EDIT_PROFILE = 'edit_profile';
    public const EDIT_USERNAME = 'edit_username';
    public const MESSAGE = 'message';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof User
            && \in_array(
                $attribute,
                [self::FOLLOW, self::BLOCK, self::MESSAGE, self::EDIT_PROFILE, self::EDIT_USERNAME],
                true
            );
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if($user->isBanned) {
            return false;
        }

        return match ($attribute) {
            self::FOLLOW => $this->canFollow($subject, $user),
            self::BLOCK => $this->canBlock($subject, $user),
            self::MESSAGE => $this->canMessage($subject, $user),
            self::EDIT_PROFILE => $this->canEditProfile($subject, $user),
            self::EDIT_USERNAME => $this->canEditUsername($subject, $user),
            default => throw new \LogicException(),
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

    private function canEditUsername(User $subject, User $user): bool
    {
        return $this->canEditProfile($subject, $user)
            && !$user->entries->count()
            && !$user->entryComments->count()
            && !$user->posts->count()
            && !$user->postComments->count()
            && !$user->subscriptions->count()
            && !$user->followers->count()
            && !$user->follows->count();
    }
}
