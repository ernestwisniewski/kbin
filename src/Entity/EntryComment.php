<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Contracts\Votable;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\EntryCommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

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
    private ?Entry $entry;

    /**
     * @ORM\ManyToOne(targetEntity="EntryComment", inversedBy="children")
     */
    private ?EntryComment $parent;

    /**
     * @ORM\Column(type="text")
     */
    private string $body;

    /**
     * @ORM\OneToMany(targetEntity="EntryComment", mappedBy="parent", cascade={"remove"})
     */
    private Collection $children;

    /**
     * @ORM\OneToMany(targetEntity=EntryCommentVote::class, mappedBy="comment",cascade={"persist"},
     *     fetch="EXTRA_LAZY", cascade={"persist"}, orphanRemoval=true))
     */
    private Collection $votes;

    public function __construct(string $body, ?Entry $entry, User $user, ?EntryComment $parent = null)
    {
        $this->body  = $body;
        $this->entry = $entry;
        $this->user  = $user;
        $this->parent = $parent;

        $this->createdAtTraitConstruct();

        $this->votes = new ArrayCollection();
        $this->children = new ArrayCollection();
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

    public function getEntry(): ?Entry
    {
        return $this->entry;
    }

    public function setEntry(?Entry $entry): self
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

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryCommentVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setComment($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryCommentVote::class);

        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getComment() === $this) {
                $vote->setComment(null);
            }
        }

        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getChildrenRecursive(int &$startIndex = 0): \Traversable {
        foreach ($this->children as $child) {
            yield $startIndex++ => $child;
            yield from $child->getChildrenRecursive($startIndex);
        }
    }
}
