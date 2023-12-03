<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Entry\EventSubscriber;

use App\Kbin\Domain\DomainExtract;
use App\Kbin\Entry\EventSubscriber\Event\EntryCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class EntryDomainSubscriber
{
    public function __construct(
        private DomainExtract $domainExtract,
    ) {
    }

    #[AsEventListener(event: EntryCreatedEvent::class)]
    public function attachDomain(EntryCreatedEvent $event): void
    {
        ($this->domainExtract)($event->entry);
    }
}
