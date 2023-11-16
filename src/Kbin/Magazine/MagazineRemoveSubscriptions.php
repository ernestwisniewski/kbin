<?php

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;

readonly class MagazineRemoveSubscriptions
{
    public function __construct(
        private MagazineUnsubscribe $magazineUnsubscribe
    ) {
    }

    public function __invoke(Magazine $magazine): void
    {
        foreach ($magazine->subscriptions as $subscription) {
            ($this->magazineUnsubscribe)($subscription->magazine, $subscription->user);
        }
    }
}
