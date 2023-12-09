<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\SpamProtection;

use App\Entity\User;
use App\Kbin\SpamProtection\Exception\SpamProtectionVerificationFailed;
use App\Kbin\User\UserReputationGet;
use App\Service\SettingsManager;

readonly class SpamProtectionCheck
{
    public function __construct(
        private UserReputationGet $userReputationGet,
        private SettingsManager $settingsManager
    ) {
    }

    public function __invoke(User $user, bool $throw = true): bool
    {
        if ($this->settingsManager->get('KBIN_SPAM_PROTECTION')) {
            if (($this->userReputationGet)($user) < 2 && $user->spamProtection) {
                if ($throw) {
                    throw new SpamProtectionVerificationFailed('User has spam protection enabled and reputation is too low '.$user->getUsername().' '.$user->getId());
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}
