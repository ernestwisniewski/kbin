<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Magazine\MagazineSubscribe;
use App\Kbin\Magazine\MagazineUnsubscribe;
use App\Kbin\User\UserFollow;
use App\Kbin\User\UserFollowAccept;
use App\Kbin\User\UserFollowReject;
use App\Kbin\User\UserUnfollow;
use App\Message\ActivityPub\Inbox\FollowMessage;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Wrapper\AcceptWrapper;
use App\Service\ActivityPubManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FollowHandler
{
    public function __construct(
        private readonly ActivityPubManager $activityPubManager,
        private readonly UserFollow $userFollow,
        private readonly UserUnfollow $userUnfollow,
        private readonly UserFollowAccept $userFollowAccept,
        private readonly UserFollowReject $userFollowReject,
        private readonly MagazineSubscribe $magazineSubscribe,
        private readonly MagazineUnsubscribe $magazineUnsubscribe,
        private readonly ApHttpClient $client,
        private readonly AcceptWrapper $acceptWrapper
    ) {
    }

    public function __invoke(FollowMessage $message)
    {
        $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

        if ('Follow' === $message->payload['type']) {
            $object = $this->activityPubManager->findActorOrCreate($message->payload['object']);

            $this->handleFollow($object, $actor);

            // @todo manually Accept
            $this->accept($message->payload, $object);

            return;
        }

        if (isset($message->payload['object'])) {
            switch ($message->payload['type']) {
                case 'Undo':
                    $this->handleUnfollow(
                        $this->activityPubManager->findActorOrCreate($message->payload['object']['object']),
                        $actor
                    );
                    break;
                case 'Accept':
                    $this->handleAccept(
                        $actor,
                        $this->activityPubManager->findActorOrCreate($message->payload['object']['actor'])
                    );
                    break;
                case 'Reject':
                    $this->handleReject(
                        $actor,
                        $this->activityPubManager->findActorOrCreate($message->payload['object']['actor'])
                    );
                    break;
                default:
                    break;
            }
        }
    }

    private function handleFollow(User|Magazine $object, User $actor): void
    {
        match (true) {
            $object instanceof User => ($this->userFollow)($actor, $object),
            $object instanceof Magazine => ($this->magazineSubscribe)($object, $actor),
            default => throw new \LogicException(),
        };
    }

    #[ArrayShape([
        '@context' => 'string',
        'id' => 'string',
        'type' => 'string',
        'actor' => 'mixed',
        'object' => 'mixed',
    ])]
    private function accept(
        array $payload,
        User|Magazine $object
    ): void {
        $accept = $this->acceptWrapper->build(
            $payload['object'],
            $payload['actor'],
            $payload['id'],
        );

        $this->client->post($this->client->getInboxUrl($payload['actor']), $object, $accept);
    }

    private function handleUnfollow(User|Magazine $object, User $actor): void
    {
        match (true) {
            $object instanceof User => ($this->userUnfollow)($actor, $object),
            $object instanceof Magazine => ($this->magazineUnsubscribe)($object, $actor),
            default => throw new \LogicException(),
        };
    }

    private function handleAccept(User $actor, User|Magazine $object): void
    {
        if ($object instanceof User) {
            ($this->userFollowAccept)($object, $actor);
        }
    }

    private function handleReject(User $actor, User|Magazine $object): void
    {
        match (true) {
            $object instanceof User => ($this->userFollowReject)($object, $actor),
            $object instanceof Magazine => ($this->magazineUnsubscribe)($object, $actor),
            default => throw new \LogicException(),
        };
    }
}
