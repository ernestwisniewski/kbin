<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\CommentInterface;
use App\Entity\Contracts\RankingInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\RankingTrait;
use App\Entity\Traits\VisibilityTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post implements VoteInterface, CommentInterface, VisibilityInterface, RankingInterface
{
    use VotableTrait;
    use RankingTrait;
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private ?Magazine $magazine;

    /**
     * @ORM\ManyToOne(targetEntity="Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Image $image = null;

    /**
     * @ORM\Column(type="text", nullable=true, length=15000)
     */
    private ?string $body = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $commentCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $score = 0;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private ?\DateTime $lastActive;

    /**
     * @ORM\OneToMany(targetEntity=PostComment::class, mappedBy="post", orphanRemoval=true)
     */
    private Collection $comments;

    /**
     * @ORM\OneToMany(targetEntity=PostVote::class, mappedBy="post", cascade={"persist"},
     *     fetch="EXTRA_LAZY", orphanRemoval=true)
     */
    private Collection $votes;

    public function __construct(string $body, Magazine $magazine, User $user)
    {
        $this->body     = $body;
        $this->magazine = $magazine;
        $this->user     = $user;
        $this->comments = new ArrayCollection();
        $this->votes    = new ArrayCollection();

        $user->addPost($this);

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

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    public function getCommentCount(): int
    {
        return $this->commentCount;
    }

    public function setCommentCount(int $commentCount): self
    {
        $this->commentCount = $commentCount;

        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getBestComments(): Collection
    {
        return new ArrayCollection($this->comments->slice(0, 2));
    }

    public function getLastComments(): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['id' => 'DESC']);

        return new ArrayCollection($this->comments->matching($criteria)->slice(0, 2));
    }

    public function addComment(PostComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        $this->updateCounts();
        $this->updateRanking();
        $this->updateLastActive();

        return $this;
    }

    public function removeComment(PostComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        $this->updateCounts();
        $this->updateRanking();
        $this->updateLastActive();

        return $this;
    }

    private function updateCounts(): self
    {
        $this->setCommentCount(
            $this->getComments()->count()
        );

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
        $this->comments->get(-1);

        $criteria = Criteria::create()
            ->orderBy(['createdAt' => 'DESC'])
            ->setMaxResults(1);

        $lastComment = $this->comments->matching($criteria)->first();

        if ($lastComment) {
            $this->lastActive = \DateTime::createFromImmutable($lastComment->getCreatedAt());
        } else {
            $this->lastActive = \DateTime::createFromImmutable($this->getCreatedAt());
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

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, PostVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setPost($this);
        }

        $this->score = $this->getUpVotes()->count() - $this->getDownVotes()->count();
        $this->updateRanking();

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, PostVote::class);

        if ($this->votes->removeElement($vote)) {
            if ($vote->getPost() === $this) {
                $vote->setPost(null);
            }
        }

        $this->score = $this->getUpVotes()->count() - $this->getDownVotes()->count();
        $this->updateRanking();

        return $this;
    }

    public function __sleep()
    {
        return [];
    }
}
