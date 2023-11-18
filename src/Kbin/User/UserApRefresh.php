<?php

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Service\ActivityPubManager;

readonly class UserApRefresh
{
    public function __construct(
        private readonly UserAvatarDetach $userAvatarDetach,
        private readonly UserCoverDetach $userCoverDetach,
        private readonly ActivityPubManager $activityPubManager
    ) {
    }

    public function __invoke(User $user): void
    {
        ($this->userAvatarDetach)($user);
        ($this->userCoverDetach)($user);

        $this->activityPubManager->updateUser($user->apProfileId);
    }
}
