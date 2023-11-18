<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Kbin\Magazine\MessageBus\MagazinePurgeMessage;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class MagazinePurge
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(Magazine $magazine, bool $contentOnly = false): void
    {
        $this->messageBus->dispatch(new MagazinePurgeMessage($magazine->getId(), $contentOnly));
    }
}
