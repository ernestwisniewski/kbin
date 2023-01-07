<?php

declare(strict_types=1);

namespace App\EventSubscriber\Api;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

// @todo serialization temporary fix
final class BestCommentsFixApiSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['transform', EventPriorities::POST_SERIALIZE],
        ];
    }

    public function transform(ViewEvent $event): void
    {
        if ('/api/posts' !== $event->getRequest()->getPathInfo()) {
            return;
        }

        $results = json_decode($event->getControllerResult(), true);

        foreach ($results['hydra:member'] as $index => $member) {
            $results['hydra:member'][$index]['bestComments'] = array_values($member['bestComments']);
        }

        $event->setControllerResult(json_encode($results));
    }
}
