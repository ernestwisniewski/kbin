<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Traits\VisibilityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\EntryCommentRepository;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\VotableTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity(repositoryClass=EntryCommentRepository::class)
 */
class EntryComment implements VoteInterface, VisibilityInterface
{
    use VotableTrait;
    use VisibilityTrait;
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
     * @ORM\ManyToOne(targetEntity=Magazine::class)
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private ?Magazine $magazine;

    /**
     * @ORM\ManyToOne(targetEntity="EntryComment", inversedBy="children", cascade={"remove"})
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private ?EntryComment $parent;

    /**
     * @ORM\ManyToOne(targetEntity="EntryComment")
     */
    private ?EntryComment $root = null;

    /**
     * @ORM\Column(type="text", length=4500)
     */
    private string $body;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private \DateTime $lastActive;

    /**
     * @ORM\OneToMany(targetEntity="EntryComment", mappedBy="parent", orphanRemoval=true)
     */
    private Collection $children;

    /**
     * @ORM\OneToMany(targetEntity=EntryCommentVote::class, mappedBy="comment",
     *     fetch="EXTRA_LAZY", cascade={"persist"}, orphanRemoval=true))
     */
    private Collection $votes;

    public function __construct(string $body, ?Entry $entry, User $user, ?EntryComment $parent = null)
    {
        $this->body   = $body;
        $this->entry  = $entry;
        $this->user   = $user;
        $this->parent = $parent;

        if ($parent) {
            $this->root = $parent->getRoot() ?? $parent;
        }

        $this->createdAtTraitConstruct();
        $this->updateLastActive();

        $this->votes    = new ArrayCollection();
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

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(?Magazine $magazine): self
    {
        $this->magazine = $magazine;

        return $this;
    }

    public function getParent(): ?EntryComment
    {
        return $this->parent;
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

    public function getLastActive(): ?\DateTime
    {
        return $this->lastActive;
    }

    public function setLastActive(\DateTime $lastActive): self
    {
        $this->lastActive = $lastActive;

        return $this;
    }

    public function updateLastActive(): void
    {
        $this->setLastActive(\DateTime::createFromImmutable($this->getCreatedAt()));

        $root = $this->getRoot();
        if (!$root) {
            return;
        }

        $root->setLastActive(\DateTime::createFromImmutable($this->getCreatedAt()));
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryCommentVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
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

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getChildrenRecursive(int &$startIndex = 0): \Traversable
    {
        foreach ($this->children as $child) {
            yield $startIndex++ => $child;
            yield from $child->getChildrenRecursive($startIndex);
        }
    }

    public function getRoot(): ?EntryComment
    {
        return $this->root;
    }

    public function setRoot(?EntryComment $root): self
    {
        $this->root = $root;

        return $this;
    }

    public function softDelete(): void
    {
        $this->visibility = self::VISIBILITY_SOFT_DELETED;
    }

    public function trash(): void
    {
        $this->visibility = self::VISIBILITY_TRASHED;
    }

    public function restore(): void
    {
        $this->visibility = self::VISIBILITY_VISIBLE;
    }

    public function __sleep()
    {
        return [];
    }
}
