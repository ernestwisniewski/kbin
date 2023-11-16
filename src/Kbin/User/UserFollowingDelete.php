<?php

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;

class UserFollowingDelete
{
    public function __construct(
        private UserUnfollow $userUnfollow
    ) {
    }

    public function __invoke(User $user): void
    {
        foreach ($user->follows as $follow) {
            ($this->userUnfollow)($user, $follow->following);
        }
    }
}
