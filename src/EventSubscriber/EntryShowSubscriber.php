<?php declare(strict_types=1);

namespace App\EventSubscriber;

use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchoulom\ViewCounterBundle\Counter\ViewCounter as Counter;
use App\Event\EntryHasBeenSeenEvent;

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
            $this->viewCounter->saveView($event->entry);
        } catch (Exception $e) {

        }
    }
}
