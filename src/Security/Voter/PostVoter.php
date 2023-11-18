<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PostVoter extends Voter
{
    public const CREATE = 'create';
    public const EDIT = 'edit';
    public const DELETE = 'delete';
    public const PURGE = 'purge';
    public const COMMENT = 'comment';
    public const VOTE = 'vote';
    public const MODERATE = 'moderate';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Post
            && \in_array(
                $attribute,
                [self::CREATE, self::EDIT, self::DELETE, self::PURGE, self::COMMENT, self::VOTE, self::MODERATE],
                true
            );
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::PURGE => $this->canPurge($subject, $user),
            self::COMMENT => $this->canComment($subject, $user),
            self::VOTE => $this->canVote($subject, $user),
            self::MODERATE => $this->canModerate($subject, $user),
            default => throw new \LogicException(),
        };
    }

    private function canEdit(Post $post, User $user): bool
    {
        if ($post->user === $user) {
            return true;
        }

        return false;
    }

    private function canDelete(Post $post, User $user): bool
    {
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        if ($post->user === $user) {
            return true;
        }

        if ($post->magazine->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canPurge(Post $post, User $user): bool
    {
        return $user->isAdmin();
    }

    private function canComment(Post $post, User $user): bool
    {
        return !$post->magazine->isBanned($user);
    }

    private function canVote(Post $post, User $user): bool
    {
        //        if ($post->user === $user) {
        //            return false;
        //        }

        if ($post->magazine->isBanned($user)) {
            return false;
        }

        return true;
    }

    private function canModerate(Post $post, User $user): bool
    {
        return $post->magazine->userIsModerator($user) || $user->isAdmin() || $user->isModerator();
    }
}
