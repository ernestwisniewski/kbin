<?php declare(strict_types=1);

namespace App\Controller\ActivityPub;

use ActivityPhp\Type\Core\Activity;
use App\ActivityPub\JsonRd;
use App\Event\ActivityPub\WebfingerResponseEvent;
use App\Message\ActivityPub\Inbox\ActivityMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class WebfingerController
{
    public function __construct(private EventDispatcherInterface $eventDispatcher, private MessageBusInterface $bus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->bus->dispatch(new ActivityMessage('{"@context":"https://www.w3.org/ns/activitystreams","id":"https://mastodon.krbn.pl/27fcfcb2-fc20-4ace-8e06-75530ea586d1","type":"Follow","actor":"https://mastodon.krbn.pl/users/ernest","object":"https://dev.karab.in/u/ernest"}'));
        $event = new WebfingerResponseEvent((new JsonRd()));
        $this->eventDispatcher->dispatch($event);

        if (!empty($event->jsonRd->getLinks())) {
            $response = new JsonResponse($event->jsonRd->toArray());
        } else {
            $response = new JsonResponse();
            $response->setStatusCode(404);
            $response->headers->set('Status', '404 Not Found');
        }

        $response->headers->set('Content-Type', 'application/jrd+json; charset=utf-8');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
