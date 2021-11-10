<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\VoteEvent;
use App\Message\Notification\VoteNotificationMessage;
use Doctrine\Common\Util\ClassUtils;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class VoteHandleSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    #[ArrayShape([VoteEvent::class => "string"])] public static function getSubscribedEvents(): array
    {
        return [
            VoteEvent::class => 'onVote',
        ];
    }

    public function onVote(VoteEvent $event): void
    {
        $this-
        $this->bus->dispatch(
            (
            new VoteNotificationMessage(
                $event->votable->getId(),
                ClassUtils::getRealClass(get_class($event->votable))
            ))
        );
    }
}
