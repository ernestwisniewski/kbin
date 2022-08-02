<?php declare(strict_types=1);

namespace App\Controller\ActivityPub;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class InboxController
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public function __invoke(Request $request)
    {
        $headers = $request->headers;
        $payload = $request->getContent();


    }
}
