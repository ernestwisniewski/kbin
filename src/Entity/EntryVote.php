<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EntryVoteRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="entryVotes")
 * })
 */
class EntryVote extends Vote
{
    /**
     * @ORM\JoinColumn(name="entry_id", nullable=false)
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="votes")
     */
    private Entry $entry;

    public function __construct(int $choice, User $user, Entry $entry)
    {
        parent::__construct($choice, $user);

        $this->entry = $entry;
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry): self
    {
        $this->entry = $entry;
    }

}
