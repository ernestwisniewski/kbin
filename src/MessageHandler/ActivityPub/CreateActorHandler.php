<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub;

use App\Message\ActivityPub\CreateActorMessage;
use App\Service\ActivityPubManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateActorHandler
{
    public function __construct(private readonly ActivityPubManager $activityPubManager)
    {
    }

    public function __invoke(CreateActorMessage $message): void
    {
        try {
            $this->activityPubManager->findActorOrCreate($message->handle);
        } catch (\Exception $e) {
        }
    }
}
