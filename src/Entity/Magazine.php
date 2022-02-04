<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\VisibilityTrait;
use App\Repository\MagazineRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="magazine_name_idx",
 *         columns={"name"}
 *     )
 * })
 * @ORM\Entity(repositoryClass=MagazineRepository::class)
 */
class Magazine implements VisibilityInterface
{
    use VisibilityTrait;
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    public ?Image $cover = null;
    /**
     * @ORM\Column(type="string", length=25)
     */
    public string $name;
    /**
     * @ORM\Column(type="string", length=50)
     */
    public ?string $title;
    /**
     * @ORM\Column(type="text", nullable=true, length=420)
     */
    public ?string $description = null;
    /**
     * @ORM\Column(type="text", nullable=true, length=420)
     */
    public ?string $rules = null;
    /**
     * @ORM\Column(type="integer")
     */
    public int $subscriptionsCount = 0;
    /**
     * @ORM\Column(type="integer")
     */
    public int $entryCount = 0;
    /**
     * @ORM\Column(type="integer")
     */
    public int $entryCommentCount = 0;
    /**
     * @ORM\Column(type="integer")
     */
    public int $postCount = 0;
    /**
     * @ORM\Column(type="integer")
     */
    public int $postCommentCount = 0;
    /**
     * @ORM\Column(type="boolean")
     */
    public ?bool $isAdult = false;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $customCss = null;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $customJs = null;
    /**
     * @ORM\OneToMany(targetEntity=Moderator::class, mappedBy="magazine", cascade={"persist"})
     */
    public Collection $moderators;
    /**
     * @ORM\OneToMany(targetEntity=Entry::class, mappedBy="magazine")
     */
    public Collection $entries;
    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="magazine")
     */
    public Collection $posts;
    /**
     * @ORM\OneToMany(targetEntity=MagazineSubscription::class, mappedBy="magazine", orphanRemoval=true, cascade={"persist", "remove"})
     */
    public Collection $subscriptions;
    /**
     * @ORM\OneToMany(targetEntity=MagazineBan::class, mappedBy="magazine", orphanRemoval=true, cascade={"persist", "remove"})
     */
    public Collection $bans;
    /**
     * @ORM\OneToMany(targetEntity="Report", mappedBy="magazine", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id": "DESC"})
     */
    public Collection $reports;
    /**
     * @ORM\OneToMany(targetEntity="Badge", mappedBy="magazine", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id": "DESC"})
     */
    public Collection $badges;
    /**
     * @ORM\OneToMany(targetEntity="MagazineLog", mappedBy="magazine", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id": "DESC"})
     */
    public Collection $logs;
    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    public ?DateTime $lastActive = null;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(string $name, string $title, User $user, ?string $description, ?string $rules, ?bool $isAdult, ?Image $cover)
    {
        $this->name          = $name;
        $this->title         = $title;
        $this->description   = $description;
        $this->rules         = $rules;
        $this->isAdult       = $isAdult ?? false;
        $this->cover         = $cover;
        $this->moderators    = new ArrayCollection();
        $this->entries       = new ArrayCollection();
        $this->posts         = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->bans          = new ArrayCollection();
        $this->reports       = new ArrayCollection();
        $this->badges        = new ArrayCollection();
        $this->logs          = new ArrayCollection();

        $this->addModerator(new Moderator($this, $user, true, true));

        $this->createdAtTraitConstruct();
    }

    public function addModerator(Moderator $moderator): self
    {
        if (!$this->moderators->contains($moderator)) {
            $this->moderators->add($moderator);
            $moderator->magazine = $this;
        }

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function userIsModerator(User $user): bool
    {
        $user->moderatorTokens->get(-1);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('magazine', $this))
            ->andWhere(Criteria::expr()->eq('isConfirmed', true));

        return !$user->moderatorTokens->matching($criteria)->isEmpty();
    }

    public function userIsOwner(User $user): bool
    {
        $user->moderatorTokens->get(-1);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('magazine', $this))
            ->andWhere(Criteria::expr()->eq('isOwner', true));

        return !$user->moderatorTokens->matching($criteria)->isEmpty();
    }

    public function getOwner(): User
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('isOwner', true));

        return $this->moderators->matching($criteria)->first()->user;
    }

    public function addEntry(Entry $entry): self
    {
        if (!$this->entries->contains($entry)) {
            $this->entries->add($entry);
            $entry->magazine = $this;
        }

        $this->updateEntryCounts();

        return $this;
    }

    public function updateEntryCounts(): self
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('visibility', Entry::VISIBILITY_VISIBLE));

        $this->entryCount = $this->entries->matching($criteria)->count();

        return $this;
    }

    public function removeEntry(Entry $entry): self
    {
        if ($this->entries->removeElement($entry)) {
            if ($entry->magazine === $this) {
                $entry->magazine = null;
            }
        }

        $this->updateEntryCounts();

        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->magazine = $this;
        }

        $this->updatePostCounts();

        return $this;
    }

    private function updatePostCounts(): self
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('visibility', Entry::VISIBILITY_VISIBLE));

        $this->postCount = $this->posts->matching($criteria)->count();

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            if ($post->magazine === $this) {
                $post->magazine = null;
            }
        }

        $this->updatePostCounts();

        return $this;
    }

    public function subscribe(User $user): self
    {
        if (!$this->isSubscribed($user)) {
            $this->subscriptions->add($sub = new MagazineSubscription($user, $this));
            $sub->magazine = $this;
        }

        $this->updateSubscriptionsCount();

        return $this;
    }

    public function isSubscribed(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return $this->subscriptions->matching($criteria)->count() > 0;
    }

    private function updateSubscriptionsCount(): void
    {
        $this->subscriptionsCount = $this->subscriptions->count();
    }

    public function unsubscribe(User $user): void
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        $subscription = $this->subscriptions->matching($criteria)->first();

        if ($this->subscriptions->removeElement($subscription)) {
            if ($subscription->magazine === $this) {
                $subscription->magazine = null;
            }
        }

        $this->updateSubscriptionsCount();
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

    public function addBan(User $user, User $bannedBy, ?string $reason, ?DateTimeInterface $expiredAt): ?MagazineBan
    {
        $ban = $this->isBanned($user);

        if (!$ban) {
            $this->bans->add($ban = new MagazineBan($this, $user, $bannedBy, $reason, $expiredAt));
            $ban->magazine = $this;
        } else {
            return null;
        }

        return $ban;
    }

    public function isBanned(User $user): bool
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->gt('expiredAt', new DateTime()))
            ->orWhere(Criteria::expr()->isNull('expiredAt'))
            ->andWhere(Criteria::expr()->eq('user', $user));

        return $this->bans->matching($criteria)->count() > 0;
    }

    public function removeBan(MagazineBan $ban): self
    {
        if ($this->bans->removeElement($ban)) {
            if ($ban->magazine === $this) {
                $ban->magazine = null;
            }
        }

        return $this;
    }

    public function unban(User $user): MagazineBan
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->gt('expiredAt', new DateTime()))
            ->orWhere(Criteria::expr()->isNull('expiredAt'))
            ->andWhere(Criteria::expr()->eq('user', $user));

        /**
         * @var MagazineBan $ban
         */
        $ban            = $this->bans->matching($criteria)->first();
        $ban->expiredAt = new DateTime('+10 seconds');

        return $ban;
    }

    public function addBadge(Badge ...$badges): self
    {
        foreach ($badges as $badge) {
            if (!$this->badges->contains($badge)) {
                $this->badges->add($badge);
            }
        }

        return $this;
    }

    public function removeBadge(Badge $badge): self
    {
        $this->badges->removeElement($badge);

        return $this;
    }

    public function addLog(MagazineLog $log): void
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
        }
    }

    public function __sleep()
    {
        return [];
    }
}
