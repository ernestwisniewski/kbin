<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\User;
use App\Message\ActivityPub\Inbox\ActivityMessage;
use App\Message\ActivityPub\Inbox\AnnounceMessage;
use App\Message\ActivityPub\Inbox\CreateMessage;
use App\Message\ActivityPub\Inbox\DeleteMessage;
use App\Message\ActivityPub\Inbox\FollowMessage;
use App\Message\ActivityPub\Inbox\LikeMessage;
use App\Message\ActivityPub\Inbox\UpdateMessage;
use App\Service\ActivityPub\SignatureValidator;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
readonly class ActivityHandler
{
    public function __construct(
        private SignatureValidator $signatureValidator,
        private SettingsManager $settingsManager,
        private MessageBusInterface $bus,
        private ActivityPubManager $manager,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ActivityMessage $message): void
    {
        $payload = @json_decode($message->payload, true);

        if ($message->request && $message->headers) {
            $this->signatureValidator->validate($message->request, $message->headers, $message->payload);
        }

        if (isset($payload['payload'])) {
            $payload = $payload['payload'];
        }

        try {
            if (isset($payload['actor']) || isset($payload['attributedTo'])) {
                if (!$this->verifyInstanceDomain($payload['actor'] ?? $payload['attributedTo'])) {
                    return;
                }
                $user = $this->manager->findActorOrCreate($payload['actor'] ?? $payload['attributedTo']);
            } else {
                if (!$this->verifyInstanceDomain($payload['id'])) {
                    return;
                }
                $user = $this->manager->findActorOrCreate($payload['id']);
            }
        } catch (\Exception $e) {
            $this->logger->error('payload: '.json_encode($payload));

            return;
        }

        if ($user instanceof User && $user->isBanned) {
            return;
        }

        $this->handle($payload);
    }

    private function handle(array $payload)
    {
        if ('Announce' === $payload['type']) {
            if (\is_array($payload['object'])) {
                $payload = $payload['object'];
            }
        }

        switch ($payload['type']) {
            case 'Create':
                $this->bus->dispatch(new CreateMessage($payload['object']));
                break;
            case 'Note':
            case 'Page':
            case 'Article':
            case 'Question':
                $this->bus->dispatch(new CreateMessage($payload));
                // no break
            case 'Announce':
                $this->bus->dispatch(new AnnounceMessage($payload));
                break;
            case 'Like':
                $this->bus->dispatch(new LikeMessage($payload));
                break;
            case 'Follow':
                $this->bus->dispatch(new FollowMessage($payload));
                break;
            case 'Delete':
                $this->bus->dispatch(new DeleteMessage($payload));
                break;
            case 'Undo':
                $this->handleUndo($payload);
                break;
            case 'Accept':
            case 'Reject':
                $this->handleAcceptAndReject($payload);
                break;
            case 'Update':
                $this->bus->dispatch(new UpdateMessage($payload));
                break;
        }
    }

    private function handleUndo(array $payload): void
    {
        if (\is_array($payload['object'])) {
            $type = $payload['object']['type'];
        } else {
            $type = $payload['type'];
        }

        if ('Follow' === $type) {
            $this->bus->dispatch(new FollowMessage($payload));

            return;
        }

        if ('Announce' === $type) {
            $this->bus->dispatch(new AnnounceMessage($payload));

            return;
        }

        if ('Like' === $type) {
            $this->bus->dispatch(new LikeMessage($payload));

            return;
        }
    }

    private function handleAcceptAndReject(array $payload): void
    {
        if (\is_array($payload['object'])) {
            $type = $payload['object']['type'];
        } else {
            $type = $payload['type'];
        }

        if ('Follow' === $type) {
            $this->bus->dispatch(new FollowMessage($payload));
        }
    }

    private function verifyInstanceDomain(string $id): bool
    {
        if (\in_array(
            str_replace('www.', '', parse_url($id, PHP_URL_HOST)),
            $this->settingsManager->get('KBIN_BANNED_INSTANCES') ?? []
        )) {
            return false;
        }

        return true;
    }
}
