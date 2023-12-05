<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Vote\EventSubscriber;

use App\Entity\Contracts\VotableInterface;
use App\Kbin\Favourite\FavouriteToggle;
use App\Kbin\Vote\EventSubscriber\Event\VoteEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class VoteHandleSubscriber
{
    public function __construct(private FavouriteToggle $favouriteToggle)
    {
    }

    #[AsEventListener(event: VoteEvent::class)]
    public function onVote(VoteEvent $event): void
    {
        if (VotableInterface::VOTE_DOWN === $event->vote->choice) {
            ($this->favouriteToggle)($event->vote->user, $event->votable, FavouriteToggle::TYPE_UNLIKE);
        }
    }
}
