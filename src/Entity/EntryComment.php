<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Contracts\ReportInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\VisibilityTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\EntryCommentRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity(repositoryClass=EntryCommentRepository::class)
 */
class EntryComment implements VoteInterface, VisibilityInterface, ReportInterface, FavouriteInterface
{
    use VotableTrait;
    use VisibilityTrait;
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="entryComments")
     * @ORM\JoinColumn(nullable=false)
     */
    public User $user;
    /**
     * @ORM\ManyToOne(targetEntity=Entry::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    public ?Entry $entry;
    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class)
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    public ?Magazine $magazine;
    /**
     * @ORM\ManyToOne(targetEntity="Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    public ?Image $image = null;
    /**
     * @ORM\ManyToOne(targetEntity="EntryComment", inversedBy="children")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    public ?EntryComment $parent;
    /**
     * @ORM\ManyToOne(targetEntity="EntryComment")
     */
    public ?EntryComment $root = null;
    /**
     * @ORM\Column(type="text", length=4500)
     */
    public ?string $body;
    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    public int $favouriteCount = 0;
    /**
     * @ORM\Column(type="datetimetz")
     */
    public DateTime $lastActive;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public ?string $ip = null;
    /**
     * @ORM\Column(type="array", nullable=true, options={"default" : null})
     */
    public ?array $tags = null;
    /**
     * @ORM\OneToMany(targetEntity="EntryComment", mappedBy="parent", orphanRemoval=true)
     * @OrderBy({"id" = "ASC"})
     */
    public Collection $children;
    /**
     * @ORM\OneToMany(targetEntity=EntryCommentVote::class, mappedBy="comment",
     *     fetch="EXTRA_LAZY", cascade={"persist"}, orphanRemoval=true))
     */
    public Collection $votes;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EntryCommentReport", mappedBy="entryComment", cascade={"remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    public Collection $reports;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EntryCommentFavourite", mappedBy="entryComment", cascade={"remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    public Collection $favourites;
    /**
     * @ORM\OneToMany(targetEntity="EntryCommentCreatedNotification", mappedBy="entryComment", cascade={"remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    public Collection $notifications;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(string $body, ?Entry $entry, User $user, ?EntryComment $parent = null, ?string $ip = null)
    {
        $this->body          = $body;
        $this->entry         = $entry;
        $this->user          = $user;
        $this->parent        = $parent;
        $this->ip            = $ip;
        $this->votes         = new ArrayCollection();
        $this->children      = new ArrayCollection();
        $this->reports       = new ArrayCollection();
        $this->favourites    = new ArrayCollection();
        $this->notifications = new ArrayCollection();

        if ($parent) {
            $this->root = $parent->root ?? $parent;
        }

        $this->createdAtTraitConstruct();
        $this->updateLastActive();
    }

    public function updateLastActive(): void
    {
        $this->lastActive = DateTime::createFromImmutable($this->createdAt);

        if (!$this->root) {

            return;
        }

        $this->root->lastActive = DateTime::createFromImmutable($this->createdAt);
    }

    public function getId(): int
    {
        return $this->id;
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
            if ($vote->comment === $this) {
                $vote->setComment(null);
            }
        }

        return $this;
    }

    public function getChildrenRecursive(int &$startIndex = 0): Traversable
    {
        foreach ($this->children as $child) {
            yield $startIndex++ => $child;
            yield from $child->getChildrenRecursive($startIndex);
        }
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
        $this->visibility = VisibilityInterface::VISIBILITY_VISIBLE;
    }

    public function isAuthor(User $user): bool
    {
        return $user === $this->user;
    }

    public function getShortTitle(): string
    {
        $body = $this->body;
        preg_match('/^(.*)$/m', $body, $firstLine);
        $firstLine = $firstLine[0];

        if (grapheme_strlen($firstLine) <= 60) {
            return $firstLine;
        }

        return grapheme_substr($firstLine, 0, 60).'…';
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function updateCounts(): self
    {
        $this->favouriteCount = $this->favourites->count();

        return $this;
    }

    public function isFavored(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return $this->favourites->matching($criteria)->count() > 0;
    }

    public function __sleep()
    {
        return [];
    }
}
