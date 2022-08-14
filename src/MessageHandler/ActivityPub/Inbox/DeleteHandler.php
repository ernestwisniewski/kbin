<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Message\ActivityPub\Inbox\DeleteMessage;
use App\Service\ActivityPubManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteHandler implements MessageHandlerInterface
{
    public function __construct(private ActivityPubManager $activityPubManager)
    {
    }

    public function __invoke(DeleteMessage $message)
    {
//        $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

    }
}
