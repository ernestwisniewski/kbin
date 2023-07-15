<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Contracts\CommentInterface;
use App\Entity\Contracts\DomainInterface;
use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Contracts\RankingInterface;
use App\Entity\Contracts\ReportInterface;
use App\Entity\Contracts\TagInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Contracts\VotableInterface;
use App\Entity\Traits\ActivityPubActivityTrait;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\EditedAtTrait;
use App\Entity\Traits\RankingTrait;
use App\Entity\Traits\VisibilityTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\EntryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Tchoulom\ViewCounterBundle\Model\ViewCountable;
use Webmozart\Assert\Assert;

#[Entity(repositoryClass: EntryRepository::class)]
#[Index(columns: ['visibility', 'is_adult'], name: 'entry_visibility_adult_idx')]
#[Index(columns: ['visibility'], name: 'entry_visibility_idx')]
#[Index(columns: ['is_adult'], name: 'entry_adult_idx')]
#[Index(columns: ['ranking'], name: 'entry_ranking_idx')]
#[Index(columns: ['score'], name: 'entry_score_idx')]
#[Index(columns: ['comment_count'], name: 'entry_comment_count_idx')]
#[Index(columns: ['created_at'], name: 'entry_created_at_idx')]
#[Index(columns: ['last_active'], name: 'entry_last_active_at_idx')]
class Entry implements VotableInterface, CommentInterface, DomainInterface, VisibilityInterface, RankingInterface, ReportInterface, FavouriteInterface, ViewCountable, TagInterface, ActivityPubActivityInterface
{
    use VotableTrait;
    use RankingTrait;
    use VisibilityTrait;
    use ActivityPubActivityTrait;
    use EditedAtTrait;
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    public const ENTRY_TYPE_ARTICLE = 'article';
    public const ENTRY_TYPE_LINK = 'link';
    public const ENTRY_TYPE_IMAGE = 'image';
    public const ENTRY_TYPE_VIDEO = 'video';
    #[ManyToOne(targetEntity: User::class, inversedBy: 'entries')]
    #[JoinColumn(nullable: false)]
    public User $user;
    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'entries')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?Magazine $magazine;
    #[ManyToOne(targetEntity: Image::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true)]
    public ?Image $image = null;
    #[ManyToOne(targetEntity: Domain::class, inversedBy: 'entries')]
    #[JoinColumn(nullable: true)]
    public ?Domain $domain = null;
    #[Column(type: 'string', nullable: true)]
    public ?string $slug = null;
    #[Column(type: 'string', nullable: false)]
    public string $title;
    #[Column(type: 'string', length: 2048, nullable: true)]
    public ?string $url = null;
    #[Column(type: 'text', length: 35000, nullable: true)]
    public ?string $body = null;
    #[Column(type: 'string', nullable: false)]
    public string $type = self::ENTRY_TYPE_ARTICLE;
    #[Column(type: 'string', nullable: false)]
    public string $lang = 'en';
    #[Column(type: 'boolean', options: ['default' => false])]
    public bool $isOc = false;
    #[Column(type: 'boolean', nullable: false)]
    public bool $hasEmbed = false;
    #[Column(type: 'integer', nullable: false)]
    public int $commentCount = 0;
    #[Column(type: 'integer', options: ['default' => 0])]
    public int $favouriteCount = 0;
    #[Column(type: 'integer', nullable: false)]
    public int $score = 0;
    #[Column(type: 'integer', nullable: true)]
    public ?int $views = 0;
    #[Column(type: 'boolean', nullable: false)]
    public bool $isAdult = false;
    #[Column(type: 'boolean', nullable: false)]
    public bool $sticky = false;
    #[Column(type: 'datetimetz')]
    public ?\DateTime $lastActive = null;
    #[Column(type: 'string', nullable: true)]
    public ?string $ip = null;
    #[Column(type: 'integer', options: ['default' => 0])]
    public int $adaAmount = 0;
    #[Column(type: 'json', nullable: true, options: ['jsonb' => true])]
    public ?array $tags = null;
    #[Column(type: 'json', nullable: true, options: ['jsonb' => true])]
    public ?array $mentions = null;
    #[OneToMany(mappedBy: 'entry', targetEntity: EntryComment::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $comments;
    #[OneToMany(mappedBy: 'entry', targetEntity: EntryVote::class, cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[OrderBy(['createdAt' => 'DESC'])]
    public Collection $votes;
    #[OneToMany(mappedBy: 'entry', targetEntity: EntryReport::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $reports;
    #[OneToMany(mappedBy: 'entry', targetEntity: EntryFavourite::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[OrderBy(['createdAt' => 'DESC'])]
    public Collection $favourites;
    #[OneToMany(mappedBy: 'entry', targetEntity: EntryCreatedNotification::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $notifications;
    #[OneToMany(mappedBy: 'entry', targetEntity: ViewCounter::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $viewCounters;
    #[OneToMany(mappedBy: 'entry', targetEntity: EntryBadge::class, cascade: [
        'remove',
        'persist',
    ], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $badges;
    #[OneToMany(mappedBy: 'entry', targetEntity: EntryCardanoTx::class, cascade: [
        'remove',
        'persist',
    ], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $cardanoTx;
    public array $children = [];
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(
        string $title,
        ?string $url,
        ?string $body,
        Magazine $magazine,
        User $user,
        bool $isAdult,
        ?bool $isOc,
        ?string $lang,
        string $ip = null
    ) {
        $this->title = $title;
        $this->url = $url;
        $this->body = $body;
        $this->magazine = $magazine;
        $this->user = $user;
        $this->isAdult = $isAdult;
        $this->isOc = $isOc;
        $this->lang = $lang;
        $this->ip = $ip;
        $this->comments = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->favourites = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->viewCounters = new ArrayCollection();
        $this->badges = new ArrayCollection();
        $this->cardanoTx = new ArrayCollection();

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
            $this->lastActive = \DateTime::createFromImmutable($lastComment->createdAt);
        } else {
            $this->lastActive = \DateTime::createFromImmutable($this->createdAt);
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
        $this->favouriteCount = $this->favourites->count();

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

    public function getShortTitle(?int $length = 60): string
    {
        $body = wordwrap($this->title, $length);
        $body = explode("\n", $body);

        return trim($body[0]).(isset($body[1]) ? '...' : '');
    }

    public function getShortDesc(?int $length = 330): string
    {
        $body = wordwrap($this->body ?? '', $length);
        $body = explode("\n", $body);

        return trim($body[0]).(isset($body[1]) ? '...' : '');
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

    public function getUniqueCommentCount(): int
    {
        $users = [];
        $count = 0;
        foreach ($this->comments as $comment) {
            if (!in_array($comment->user, $users)) {
                $users[] = $comment->user;
                ++$count;
            }
        }

        return $count;
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

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews($views): self
    {
        $this->views = $views;

        return $this;
    }

    public function getAdaAmount(): string
    {
        $amount = $this->adaAmount / 1000000;

        return $amount > 0 ? (string) $amount : '';
    }

    public function isAdult(): bool
    {
        return $this->isAdult || $this->magazine->isAdult;
    }

    public function isFavored(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return $this->favourites->matching($criteria)->count() > 0;
    }

    public function getAuthorComment(): ?string
    {
        return null;
    }

    public function getDescription(): string
    {
        return ''; // @todo get first author comment
    }

    public function getTags(): array
    {
        return array_values($this->tags ?? []);
    }

    public function __sleep()
    {
        return [];
    }
}
