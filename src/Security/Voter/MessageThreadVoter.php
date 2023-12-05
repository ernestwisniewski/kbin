<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\MessageThread;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageThreadVoter extends Voter
{
    public const SHOW = 'show';
    public const REPLY = 'reply';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof MessageThread
            && \in_array(
                $attribute,
                [self::SHOW, self::REPLY],
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
            self::SHOW => $this->canShow($subject, $user),
            self::REPLY => $this->canReply($subject, $user),
            default => throw new \LogicException(),
        };
    }

    private function canShow(MessageThread $thread, User $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if (!$thread->userIsParticipant($user)) {
            return false;
        }

        return true;
    }

    private function canReply(MessageThread $thread, User $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if (!$thread->userIsParticipant($user)) {
            return false;
        }

        return true;
    }
}
