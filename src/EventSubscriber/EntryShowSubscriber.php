<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\EntryHasBeenSeenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchoulom\ViewCounterBundle\Counter\ViewCounter as Counter;

class EntryShowSubscriber implements EventSubscriberInterface
{
    public function __construct(private Counter $viewCounter)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryHasBeenSeenEvent::class => 'onShowEntry',
        ];
    }

    public function onShowEntry(EntryHasBeenSeenEvent $event): void
    {
        try {
            $this->viewCounter->saveView($event->getEntry());
        } catch (\Exception $e) {

        }
    }
}
