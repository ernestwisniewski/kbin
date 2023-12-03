<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\VotableInterface;
use Doctrine\ORM\Mapping\AssociationOverride;
use Doctrine\ORM\Mapping\AssociationOverrides;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table]
#[UniqueConstraint(name: 'user_entry_vote_idx', columns: ['user_id', 'entry_id'])]
#[AssociationOverrides([
    new AssociationOverride(name: 'user', inversedBy: 'entryVotes'),
])]
class EntryVote extends Vote
{
    #[ManyToOne(targetEntity: Entry::class, inversedBy: 'votes')]
    #[JoinColumn(name: 'entry_id', nullable: false, onDelete: 'CASCADE')]
    public ?Entry $entry = null;

    public function __construct(int $choice, User $user, ?Entry $entry)
    {
        parent::__construct($choice, $user, $entry->user);

        $this->entry = $entry;
    }

    public function getSubject(): VotableInterface
    {
        return $this->entry;
    }
}
