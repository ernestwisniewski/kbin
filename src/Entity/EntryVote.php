<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\VotableInterface;
use Doctrine\ORM\Mapping\AssociationOverride;
use Doctrine\ORM\Mapping\AssociationOverrides;
use Doctrine\ORM\Mapping\Cache;
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
#[Cache('NONSTRICT_READ_WRITE')]
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
