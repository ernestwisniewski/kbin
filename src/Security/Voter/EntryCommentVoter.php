<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\EntryComment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EntryCommentVoter extends Voter
{
    public const EDIT = 'edit';
    public const DELETE = 'delete';
    public const PURGE = 'purge';
    public const VOTE = 'vote';
    public const MODERATE = 'moderate';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof EntryComment && \in_array(
            $attribute,
            [self::EDIT, self::DELETE, self::PURGE, self::VOTE, self::MODERATE],
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
            self::PURGE => $this->canPurge($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::VOTE => $this->canVote($subject, $user),
            self::MODERATE => $this->canModerate($subject, $user),
            default => throw new \LogicException(),
        };
    }

    private function canEdit(EntryComment $comment, User $user): bool
    {
        if ($comment->user === $user) {
            return true;
        }

        return false;
    }

    private function canPurge(EntryComment $comment, User $user): bool
    {
        return $user->isAdmin();
    }

    private function canDelete(EntryComment $comment, User $user): bool
    {
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        if ($comment->user === $user) {
            return true;
        }

        if ($comment->entry->magazine->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canVote(EntryComment $comment, User $user): bool
    {
        //        if ($comment->user === $user) {
        //            return false;
        //        }

        if ($comment->entry->magazine->isBanned($user)) {
            return false;
        }

        return true;
    }

    private function canModerate(EntryComment $comment, User $user): bool
    {
        return $comment->magazine->userIsModerator($user) || $user->isAdmin() || $user->isModerator();
    }
}
