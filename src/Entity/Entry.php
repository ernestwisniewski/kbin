<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\CommentInterface;
use App\Entity\Contracts\DomainInterface;
use App\Entity\Contracts\RankingInterface;
use App\Entity\Contracts\ReportInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\RankingTrait;
use App\Entity\Traits\VisibilityTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\EntryRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    const ENTRY_TYPE_ARTICLE = 'article';
    const ENTRY_TYPE_LINK = 'link';
    const ENTRY_TYPE_IMAGE = 'image';
    const ENTRY_TYPE_VIDEO = 'video';
    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="entries")
     * @ORM\JoinColumn(nullable=false)
     */
    public User $user;
    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="entries")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    public ?Magazine $magazine;
    /**
     * @ORM\ManyToOne(targetEntity="Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    public ?Image $image = null;
    /**
     * @ORM\ManyToOne(targetEntity=Domain::class, inversedBy="entries")
     */
    public Domain $domain;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public ?string $slug = null;
    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $title;
    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    public ?string $url = null;
    /**
     * @ORM\Column(type="text", nullable=true, length=15000)
     */
    public ?string $body = null;
    /**
     * @ORM\Column(type="string")
     */
    public string $type = self::ENTRY_TYPE_ARTICLE;
    /**
     * @ORM\Column(type="boolean")
     */
    public bool $hasEmbed = false;
    /**
     * @ORM\Column(type="integer")
     */
    public int $commentCount = 0;
    /**
     * @ORM\Column(type="integer")
     */
    public int $score = 0;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public ?int $views = 0;
    /**
     * @ORM\Column(type="boolean")
     */
    public ?bool $isAdult = false;
    /**
     * @ORM\Column(type="boolean")
     */
    public bool $sticky = false;
    /**
     * @ORM\Column(type="datetimetz")
     */
    public ?DateTime $lastActive;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $ip;
    /**
     * @ORM\OneToMany(targetEntity=EntryComment::class, mappedBy="entry", orphanRemoval=true)
     */
    public Collection $comments;
    /**
     * @ORM\OneToMany(targetEntity=EntryVote::class, mappedBy="entry", cascade={"persist"},
     *     fetch="EXTRA_LAZY", orphanRemoval=true)
     */
    public Collection $votes;
    /**
     * @ORM\OneToMany(targetEntity="EntryReport", mappedBy="entry", cascade={"remove"}, orphanRemoval=true)
     */
    public Collection $reports;
    /**
     * @ORM\OneToMany(targetEntity="EntryCreatedNotification", mappedBy="entry", cascade={"remove"}, orphanRemoval=true)
     */
    public Collection $notifications;
    /**
     * @ORM\OneToMany(targetEntity="ViewCounter", mappedBy="entry", cascade={"remove"}, orphanRemoval=true)
     */
    public Collection $viewCounters;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EntryBadge", mappedBy="entry", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    public Collection $badges;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(string $title, ?string $url, ?string $body, Magazine $magazine, User $user, ?bool $isAdult = null, ?string $ip = null)
    {
        $this->title         = $title;
        $this->url           = $url;
        $this->body          = $body;
        $this->magazine      = $magazine;
        $this->user          = $user;
        $this->isAdult       = $isAdult ?? false;
        $this->ip            = $ip;
        $this->comments      = new ArrayCollection();
        $this->votes         = new ArrayCollection();
        $this->reports       = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->viewCounters  = new ArrayCollection();
        $this->badges        = new ArrayCollection();

        $user->addEntry($this);

        $this->createdAtTraitConstruct();

        $this->updateLastActive();
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
            $this->lastActive = DateTime::createFromImmutable($lastComment->createdAt);
        } else {
            $this->lastActive = DateTime::createFromImmutable($this->createdAt);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setBadges(Badge ...$badges)
    {
        $this->badges->clear();

        foreach ($badges as $badge) {
            $this->badges->add(new EntryBadge($this, $badge));
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

    public function addComment(EntryComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->entry = $this;
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

    public function removeComment(EntryComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->entry === $this) {
                $comment->entry = null;
            }
        }

        $this->updateCounts();
        $this->updateRanking();
        $this->updateLastActive();

        return $this;
    }

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->entry = $this;
        }

        $this->score = $this->getUpVotes()->count() - $this->getDownVotes()->count();
        $this->updateRanking();

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryVote::class);

        if ($this->votes->removeElement($vote)) {
            if ($vote->entry === $this) {
                $vote->entry = null;
            }
        }

        $this->score = $this->getUpVotes()->count() - $this->getDownVotes()->count();
        $this->updateRanking();

        return $this;
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
        return $user === $this->user;
    }

    public function getShortTitle(): string
    {
        $body = $this->title;
        preg_match('/^(.*)$/m', $body, $firstLine);
        $firstLine = $firstLine[0];

        if (grapheme_strlen($firstLine) <= 60) {
            return $firstLine;
        }

        return grapheme_substr($firstLine, 0, 60).'…';
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setDomain(Domain $domain): DomainInterface
    {
        $this->domain = $domain;

        return $this;
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

    public function getViews()
    {
        return $this->views;
    }

    public function setViews($views)
    {
        $this->views = $views;
    }

    public function __sleep()
    {
        return [];
    }
}
