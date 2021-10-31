<?php declare(strict_types = 1);

namespace App\Service\Notification;

trait NotificationTrait
{
    private function getUsersToNotify(array $subscriptions): array
    {
        return array_map(fn($sub) => $sub->user, $subscriptions);
    }

    private function merge(array $subs, array $follows): array
    {
        return array_merge(
            $subs,
            array_filter(
                $follows,
                function ($val) use ($subs) {
                    return !in_array($val, $subs);
                }
            )
        );
    }
}
