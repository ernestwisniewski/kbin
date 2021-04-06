<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\EntryCommentCreatedEvent;
use App\Event\EntryCreatedEvent;
use App\Event\PostCommentCreatedEvent;
use App\Message\EntryCommentNotificationMessage;
use App\Message\EntryEmbedMessage;
use App\Message\EntryNotificationMessage;
use App\Message\PostCommentNotificationMessage;
use App\Service\DomainManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostCommentCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCommentCreatedEvent::class => 'onPostCommentCreated',
        ];
    }

    public function onPostCommentCreated(PostCommentCreatedEvent $event)
    {
        $this->messageBus->dispatch(new PostCommentNotificationMessage($event->getComment()->getId()));
    }
}
