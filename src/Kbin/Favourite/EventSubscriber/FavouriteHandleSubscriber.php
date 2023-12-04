<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Favourite\EventSubscriber;

use App\Entity\Contracts\VotableInterface;
use App\Kbin\Favourite\EventSubscriber\Event\FavouriteEvent;
use App\Kbin\Vote\VoteRemove;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class FavouriteHandleSubscriber
{
    public function __construct(private VoteRemove $voteRemove)
    {
    }

    #[AsEventListener(event: FavouriteEvent::class)]
    public function onFavourite(FavouriteEvent $event): void
    {
        $subject = $event->subject;
        $choice = $event->subject->getUserVote($event->user)?->choice;
        if (VotableInterface::VOTE_DOWN === $choice && $subject->isFavored($event->user)) {
            ($this->voteRemove)($subject, $event->user);
        }
    }
}
