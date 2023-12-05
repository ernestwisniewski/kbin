<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\OAuth2UserConsent;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OAuth2UserConsentVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof OAuth2UserConsent
            && \in_array(
                $attribute,
                [self::VIEW, self::EDIT],
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
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            default => throw new \LogicException(),
        };
    }

    private function canView(OAuth2UserConsent $consent, User $user): bool
    {
        if ($consent->getUser() !== $user) {
            return false;
        }

        return true;
    }

    private function canEdit(OAuth2UserConsent $consent, User $user): bool
    {
        if ($consent->getUser() !== $user) {
            return false;
        }

        return true;
    }
}
