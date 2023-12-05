<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoter extends Voter
{
    public const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Message
            && \in_array(
                $attribute,
                [self::DELETE],
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
            self::DELETE => $this->canDelete($subject, $user),
            default => throw new \LogicException(),
        };
    }

    private function canDelete(Message $message, User $user): bool
    {
        return false;
    }
}
