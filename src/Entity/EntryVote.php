<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="user_entry_vote_idx",
 *         columns={"user_id", "entry_id"}
 *     )
 * })
 * @ORM\Entity()
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="entryVotes")
 * })
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 */
class EntryVote extends Vote
{
    /**
     * @ORM\JoinColumn(name="entry_id", nullable=false, onDelete="cascade")
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="votes")
     */
    public ?Entry $entry;

    public function __construct(int $choice, User $user, ?Entry $entry)
    {
        parent::__construct($choice, $user, $entry->user);

        $this->entry = $entry;
    }
}
