<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Notification;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NotificationVoter extends Voter
{
    public const VIEW = 'view';
    public const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Notification
            && \in_array(
                $attribute,
                [self::VIEW, self::DELETE],
                true
            );
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($user->isBanned) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => throw new \LogicException(),
        };
    }

    private function canView(Notification $notification, User $user): bool
    {
        return $notification->user->getId() === $user->getId();
    }

    private function canDelete(Notification $notification, User $user): bool
    {
        return $notification->user->getId() === $user->getId();
    }
}
