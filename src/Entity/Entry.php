<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Contracts\RankingInterface;
use App\Entity\Contracts\ReportInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Traits\RankingTrait;
use App\Entity\Traits\VisibilityTrait;
use App\Service\Contracts\ContentManager;
use App\Service\EntryManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Contracts\CommentInterface;
use App\Entity\Contracts\DomainInterface;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\EntryRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Tchoulom\ViewCounterBundle\Entity\ViewCounter;
use Tchoulom\ViewCounterBundle\Model\ViewCountable;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity(repositoryClass=EntryRepository::class)
 */
class Entry implements VoteInterface, CommentInterface, DomainInterface, VisibilityInterface, RankingInterface, ReportInterface, ViewCountable
{
    use VotableTrait;
    use RankingTrait;
    use VisibilityTrait;
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    const ENTRY_TYPE_ARTICLE = 'artykul';
    const ENTRY_TYPE_LINK = 'link';
    const ENTRY_TYPE_IMAGE = 'image';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="entries")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="entries")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private ?Magazine $magazine;

    /**
     * @ORM\ManyToOne(targetEntity="Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Image $image = null;

    /**
     * @ORM\ManyToOne(targetEntity=Domain::class, inversedBy="entries")
     */
    private Domain $domain;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    private ?string $url = null;

    /**
     * @ORM\Column(type="text", nullable=true, length=15000)
     */
    private ?string $body = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $type = self::ENTRY_TYPE_ARTICLE;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $hasEmbed = false;

    /**
     * @ORM\Column(type="integer")
     */
    private int $commentCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $score = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $views = 0;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private ?\DateTime $lastActive;

    /**
     * @ORM\OneToMany(targetEntity=EntryComment::class, mappedBy="entry", orphanRemoval=true)
     */
    private Collection $comments;

    /**
     * @ORM\OneToMany(targetEntity=EntryVote::class, mappedBy="entry", cascade={"persist"},
     *     fetch="EXTRA_LAZY", orphanRemoval=true)
     */
    private Collection $votes;

    /**
     * @ORM\OneToMany(targetEntity="EntryReport", mappedBy="entry", cascade={"remove"}, orphanRemoval=true)
     */
    private Collection $reports;

    /**
     * @ORM\OneToMany(targetEntity="EntryNotification", mappedBy="entry", cascade={"remove"}, orphanRemoval=true)
     */
    private Collection $notifications;

    /**
     * @ORM\OneToMany(targetEntity="ViewCounter", mappedBy="entry")
     */
    protected Collection $viewCounters;

    public function __construct(string $title, ?string $url, ?string $body, Magazine $magazine, User $user)
    {
        $this->title         = $title;
        $this->url           = $url;
        $this->body          = $body;
        $this->magazine      = $magazine;
        $this->user          = $user;
        $this->comments      = new ArrayCollection();
        $this->votes         = new ArrayCollection();
        $this->reports       = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->viewCounters  = new ArrayCollection();

        $user->addEntry($this);

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

    public function getMagazine(): Magazine
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

    public function getDomain(): Domain
    {
        return $this->domain;
    }

    public function setDomain(Domain $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function hasEmbed(): bool
    {
        return $this->hasEmbed;
    }

    public function setHasEmbed(bool $hasEmbed): void
    {
        $this->hasEmbed = $hasEmbed;
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

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews($views): self
    {
        $this->views = $views;

        return $this;
    }

    public function getLastActive(): ?\DateTime
    {
        return $this->lastActive;
    }

    public function updateLastActive(): void
    {
        $this->comments->get(-1);

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('visibility', VisibilityInterface::VISIBILITY_VISIBLE))
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

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(EntryComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setEntry($this);
        }

        $this->updateCounts();
        $this->updateRanking();
        $this->updateLastActive();

        return $this;
    }

    public function removeComment(EntryComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getEntry() === $this) {
                $comment->setEntry(null);
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
            ->andWhere(Criteria::expr()->eq('visibility', self::VISIBILITY_VISIBLE));

        $this->setCommentCount(
            $this->comments->matching($criteria)->count()
        );

        return $this;
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setEntry($this);
        }

        $this->score = $this->getUpVotes()->count() - $this->getDownVotes()->count();
        $this->updateRanking();

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryVote::class);

        if ($this->votes->removeElement($vote)) {
            if ($vote->getEntry() === $this) {
                $vote->setEntry(null);
            }
        }

        $this->score = $this->getUpVotes()->count() - $this->getDownVotes()->count();
        $this->updateRanking();

        return $this;
    }

    public function getViewCounters(): Collection
    {
        return $this->viewCounters;
    }

    public function addViewCounter(ViewCounter $viewCounter): self
    {
        $this->viewCounters[] = $viewCounter;

        return $this;
    }

    public function removeViewCounter(ViewCounter $viewCounter): void
    {
        $this->viewCounters->removeElement($viewCounter);
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
