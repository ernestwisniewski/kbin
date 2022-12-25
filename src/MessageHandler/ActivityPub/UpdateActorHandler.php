<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub;

use App\Message\ActivityPub\UpdateActorMessage;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateActorHandler implements MessageHandlerInterface
{
    public function __construct(
        private ActivityPubManager $manager,
    ) {
    }

    public function __invoke(UpdateActorMessage $message): void
    {
        $this->manager->updateActor($message->actorUrl);
    }
}
