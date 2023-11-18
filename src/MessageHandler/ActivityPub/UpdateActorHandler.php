<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub;

use App\Message\ActivityPub\UpdateActorMessage;
use App\Service\ActivityPubManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateActorHandler
{
    public function __construct(private readonly ActivityPubManager $manager)
    {
    }

    public function __invoke(UpdateActorMessage $message): void
    {
        $this->manager->updateActor($message->actorUrl);
    }
}
