<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\EntryHasBeenSeenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchoulom\ViewCounterBundle\Counter\ViewCounter as Counter;

class EntryShowSubscriber implements EventSubscriberInterface
{
    private Counter $viewCounter;

    public function __construct(Counter $viewCounter)
    {
        $this->viewCounter = $viewCounter;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryHasBeenSeenEvent::class => 'onShowEntry',
        ];
    }

    public function onShowEntry(EntryHasBeenSeenEvent $event)
    {
        try {
            $this->viewCounter->saveView($event->getEntry());
        } catch (\Exception) {

        }
    }
}
