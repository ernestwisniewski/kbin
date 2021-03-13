<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Contracts\ReportInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\VisibilityTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\PostCommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity(repositoryClass=PostCommentRepository::class)
 */
class PostComment implements VoteInterface, VisibilityInterface, ReportInterface
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="postComments")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private ?Post $post;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class)
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private ?Magazine $magazine;

    /**
     * @ORM\ManyToOne(targetEntity="Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Image $image = null;

    /**
     * @ORM\Column(type="text", length=4500)
     */
    private string $body;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private \DateTime $lastActive;

    /**
     * @ORM\ManyToOne(targetEntity="PostComment", inversedBy="children")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private ?PostComment $parent;

    /**
     * @ORM\OneToMany(targetEntity="PostComment", mappedBy="parent", orphanRemoval=true)
     */
    private Collection $children;

    /**
     * @ORM\OneToMany(targetEntity=PostCommentVote::class, mappedBy="comment",
     *     fetch="EXTRA_LAZY", cascade={"persist"}, orphanRemoval=true))
     */
    private Collection $votes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PostCommentReport", mappedBy="postComment", cascade={"remove"}, orphanRemoval=true)
     */
    private Collection $reports;

    public function __construct(string $body, ?Post $post, User $user, ?PostComment $parent = null)
    {
        $this->body     = $body;
        $this->post     = $post;
        $this->user     = $user;
        $this->parent   = $parent;
        $this->votes    = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->reports  = new ArrayCollection();

        $this->createdAtTraitConstruct();
        $this->updateLastActive();
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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

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

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getParent(): ?PostComment
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

        $this->post->setLastActive(\DateTime::createFromImmutable($this->getCreatedAt()));
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, PostCommentVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setComment($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, PostCommentVote::class);

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

    public function isAuthor(User $user): bool
    {
        return $user === $this->getUser();
    }

    public function __sleep()
    {
        return [];
    }
}
