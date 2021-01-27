<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Contracts\Votable;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\EntryCommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntryCommentRepository::class)
 */
class EntryComment implements Votable
{
    use VotableTrait;
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="entryComments")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Entry::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private Entry $entry;

    /**
     * @ORM\Column(type="text")
     */
    private string $body;

    /**
     * @ORM\OneToMany(targetEntity=EntryCommentVote::class, mappedBy="comment")
     */
    private Collection $votes;

    public function __construct(string $body, Entry $entry, User $user)
    {
        $this->body  = $body;
        $this->entry = $entry;
        $this->user  = $user;

        $this->createdAtTraitConstruct();

        $this->votes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(EntryCommentVote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setComment($this);
        }

        return $this;
    }

    public function removeVote(EntryCommentVote $vote): self
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getComment() === $this) {
                $vote->setComment(null);
            }
        }

        return $this;
    }
}
