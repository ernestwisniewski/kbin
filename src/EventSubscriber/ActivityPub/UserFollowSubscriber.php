<?php declare(strict_types=1);

namespace App\EventSubscriber\ActivityPub;

use App\Event\User\UserFollowEvent;
use App\Message\ActivityPub\Outbox\FollowMessage;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class UserFollowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private CacheInterface $cache
    ) {
    }

    #[ArrayShape([UserFollowEvent::class => "string"])] public static function getSubscribedEvents(): array
    {
        return [
            UserFollowEvent::class => 'onUserFollow',
        ];
    }

    public function onUserFollow(UserFollowEvent $event): void
    {
        if (!$event->follower->apId && $event->following->apId) {
            $this->bus->dispatch(
                new FollowMessage($event->follower->getId(), $event->following->getId(), $event->unfollow)
            );
        }

        $this->cache->invalidateTags(['user_follow_'.$event->follower->getId()]);
    }
}
