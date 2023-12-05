<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Magazine;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MagazineVoter extends Voter
{
    public const CREATE_CONTENT = 'create_content';
    public const EDIT = 'edit';
    public const DELETE = 'delete';
    public const PURGE = 'purge';
    public const MODERATE = 'moderate';
    public const SUBSCRIBE = 'subscribe';
    public const BLOCK = 'block';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Magazine
            && \in_array(
                $attribute,
                [
                    self::CREATE_CONTENT,
                    self::EDIT,
                    self::DELETE,
                    self::PURGE,
                    self::MODERATE,
                    self::SUBSCRIBE,
                    self::BLOCK,
                ],
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
            self::CREATE_CONTENT => $this->canCreateContent($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::PURGE => $this->canPurge($subject, $user),
            self::MODERATE => $this->canModerate($subject, $user),
            self::SUBSCRIBE => $this->canSubscribe($subject, $user),
            self::BLOCK => $this->canBlock($subject, $user),
            default => throw new \LogicException(),
        };
    }

    private function canCreateContent(Magazine $magazine, User $user): bool
    {
        return !$magazine->isBanned($user);
    }

    private function canEdit(Magazine $magazine, User $user): bool
    {
        return $magazine->userIsOwner($user) || $user->isAdmin() || $user->isModerator();
    }

    private function canDelete(Magazine $magazine, User $user): bool
    {
        return $magazine->userIsOwner($user) || $user->isAdmin() || $user->isModerator();
    }

    private function canPurge(Magazine $magazine, User $user): bool
    {
        return $user->isAdmin();
    }

    private function canModerate(Magazine $magazine, User $user): bool
    {
        return $magazine->userIsModerator($user) || $user->isAdmin() || $user->isModerator();
    }

    public function canSubscribe(Magazine $magazine, User $user): bool
    {
        return !$magazine->isBanned($user);
    }

    public function canBlock(Magazine $magazine, User $user): bool
    {
        if ($magazine->userIsOwner($user)) {
            return false;
        }

        return !$magazine->isBanned($user);
    }
}
