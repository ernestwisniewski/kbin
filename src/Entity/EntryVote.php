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
    private Entry $entry;

    public function __construct(int $choice, Votable $entry, User $user)
    {
        parent::__construct($user, $choice);

        $this->entry = $entry;
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }
}
