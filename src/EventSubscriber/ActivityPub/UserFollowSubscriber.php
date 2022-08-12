<?php declare(strict_types=1);

namespace App\EventSubscriber\ActivityPub;

use App\Event\User\UserFollowEvent;
use App\Message\ActivityPub\Outbox\FollowMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UserFollowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $bus
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserFollowEvent::class => 'onUserFollow',
        ];
    }

    public function onUserFollow(UserFollowEvent $event): void
    {
        if (!$event->follower->apId && $event->following->apId) {
            $this->bus->dispatch(new FollowMessage($event->follower->getId(), $event->following->getId(), $event->unfollow));
        }
    }
}
