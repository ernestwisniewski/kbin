<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Entity\User;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeliverHandler implements MessageHandlerInterface
{
    public function __construct(
        private ApHttpClient $client,
        private ActivityPubManager $manager,
        private SettingsManager $settingsManager,
    ) {
    }

    public function __invoke(DeliverMessage $message): void
    {
        if (!$this->settingsManager->get('KBIN_FEDERATION_ENABLED')) {
            return;
        }

        $actor = $this->manager->findActorOrCreate(
            $message->payload['object']['attributedTo'] ?? $message->payload['actor']
        );

        if (!$actor) {
            return;
        }

        if (!$actor->apId) {
            return;
        }

        if ($actor instanceof User && $actor->isBanned) {
            return;
        }

        $this->client->post($this->client->getInboxUrl($message->apProfileId), $actor, $message->payload);
    }
}
