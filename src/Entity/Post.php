<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Contracts\VisibilityInterface;
use Doctrine\Common\Collections\Collection;
use App\Entity\Contracts\CommentInterface;
use App\Entity\Contracts\RankingInterface;
use App\Entity\Contracts\ReportInterface;
use Doctrine\Common\Collections\Criteria;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Traits\VisibilityTrait;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\RankingTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post implements VoteInterface, CommentInterface, VisibilityInterface, RankingInterface, ReportInterface
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
    public User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    public ?Magazine $magazine;

    /**
     * @ORM\ManyToOne(targetEntity="Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    public ?Image $image = null;

    /**
     * @ORM\Column(type="text", nullable=true, length=15000)
     */
    public ?string $body = null;

    /**
     * @ORM\Column(type="integer")
     */
    public int $commentCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    public int $score = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    public ?bool $isAdult = false;

    /**
     * @ORM\Column(type="datetimetz")
     */
    public ?DateTime $lastActive;

    /**
     * @ORM\OneToMany(targetEntity=PostComment::class, mappedBy="post", orphanRemoval=true)
     */
    public Collection $comments;

    /**
     * @ORM\OneToMany(targetEntity=PostVote::class, mappedBy="post", cascade={"persist"},
     *     fetch="EXTRA_LAZY", orphanRemoval=true)
     */
    public Collection $votes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PostReport", mappedBy="post", cascade={"remove"}, orphanRemoval=true)
     */
    public Collection $reports;

    /**
     * @ORM\OneToMany(targetEntity="PostNotification", mappedBy="post", cascade={"remove"}, orphanRemoval=true)
     */
    public Collection $notifications;

    public function __construct(string $body, Magazine $magazine, User $user, ?bool $isAdult = false)
    {
        $this->body          = $body;
        $this->magazine      = $magazine;
        $this->user          = $user;
        $this->isAdult       = $isAdult ?? false;
        $this->comments      = new ArrayCollection();
        $this->votes         = new ArrayCollection();
        $this->reports       = new ArrayCollection();
        $this->notifications = new ArrayCollection();

        $user->addPost($this);

        $this->createdAtTraitConstruct();
        $this->updateLastActive();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBestComments(): Collection
    {
        return new ArrayCollection($this->comments->slice(0, 2));
    }

    public function getLastComments(): Collection
    {
        return new ArrayCollection($this->comments->slice(-2, 2));
    }

    public function addComment(PostComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->post = $this;
        }

        $this->updateCounts();
        $this->updateRanking();
        $this->updateLastActive();

        return $this;
    }

    public function removeComment(PostComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->post === $this) {
                $comment->post = null;
            }
        }

        $this->updateCounts();
        $this->updateRanking();
        $this->updateLastActive();

        return $this;
    }

    public function updateCounts(): self
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('visibility', VisibilityInterface::VISIBILITY_VISIBLE));

        $this->commentCount = $this->comments->matching($criteria)->count();

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
            $this->lastActive = DateTime::createFromImmutable($lastComment->createdAt);
        } else {
            $this->lastActive = DateTime::createFromImmutable($this->getCreatedAt());
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

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, PostVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->post = $this;
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

    public function getCommentCount(): int
    {
        return $this->commentCount;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function __sleep()
    {
        return [];
    }
}
