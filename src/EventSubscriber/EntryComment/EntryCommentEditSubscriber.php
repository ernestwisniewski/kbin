<?php declare(strict_types=1);

namespace App\EventSubscriber\EntryComment;

use App\Event\EntryComment\EntryCommentEditedEvent;
use App\Message\Notification\EntryCommentEditedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;

class EntryCommentEditSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security, private CacheInterface $cache, private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCommentEditedEvent::class => 'onEntryCommentEdited',
        ];
    }

    public function onEntryCommentEdited(EntryCommentEditedEvent $event): void
    {
        $this->cache->invalidateTags(['entry_comment_'.$event->comment->root?->getId() ?? $event->comment->getId()]);

        $this->bus->dispatch(new EntryCommentEditedNotificationMessage($event->comment->getId()));
    }
}
