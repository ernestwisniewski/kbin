<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Traits\VisibilityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use App\Repository\MagazineRepository;
use App\Entity\Traits\CreatedAtTrait;
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
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $title;

    /**
     * @ORM\Column(type="text", nullable=true, length=420)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="text", nullable=true, length=420)
     */
    private ?string $rules = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $subscriptionsCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $entryCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $commentCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $postCount = 0;

    /**
     * @ORM\OneToMany(targetEntity=Moderator::class, mappedBy="magazine", cascade={"persist"})
     */
    private Collection $moderators;

    /**
     * @ORM\OneToMany(targetEntity=Entry::class, mappedBy="magazine")
     */
    private Collection $entries;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="magazine")
     */
    private Collection $posts;

    /**
     * @ORM\OneToMany(targetEntity=MagazineSubscription::class, mappedBy="magazine", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private Collection $subscriptions;

    /**
     * @ORM\OneToMany(targetEntity=MagazineBan::class, mappedBy="magazine", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private Collection $bans;

    public function __construct(string $name, string $title, User $user, ?string $description, ?string $rules)
    {
        $this->name          = $name;
        $this->title         = $title;
        $this->description   = $description;
        $this->rules         = $rules;
        $this->moderators    = new ArrayCollection();
        $this->entries       = new ArrayCollection();
        $this->posts         = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->bans          = new ArrayCollection();

        $this->addModerator(new Moderator($this, $user, true));

        $this->createdAtTraitConstruct();
    }

    public function userIsModerator(User $user): bool
    {
        $user->getModeratorTokens()->get(-1);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('magazine', $this));

        return !$user->getModeratorTokens()->matching($criteria)->isEmpty();
    }

    public function userIsOwner(User $user): bool
    {
        $user->getModeratorTokens()->get(-1);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('magazine', $this))
            ->andWhere(Criteria::expr()->eq('isOwner', true));

        return !$user->getModeratorTokens()->matching($criteria)->isEmpty();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRules(): ?string
    {
        return $this->rules;
    }

    public function setRules(?string $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    public function getModerators(): Collection
    {
        return $this->moderators;
    }

    public function addModerator(Moderator $moderator): self
    {
        if (!$this->moderators->contains($moderator)) {
            $this->moderators->add($moderator);
            $moderator->setMagazine($this);
        }

        return $this;
    }

    public function getOwner(): User
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('isOwner', true));

        return $this->moderators->matching($criteria)->first()->getUser();
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(Entry $entry): self
    {
        if (!$this->entries->contains($entry)) {
            $this->entries->add($entry);
            $entry->setMagazine($this);
        }

        $this->updateEntryCounts();

        return $this;
    }

    public function removeEntry(Entry $entry): self
    {
        if ($this->entries->removeElement($entry)) {
            if ($entry->getMagazine() === $this) {
                $entry->setMagazine(null);
            }
        }

        $this->updateEntryCounts();

        return $this;
    }

    public function getEntryCount(): ?int
    {
        return $this->entryCount;
    }

    public function setEntryCount(int $entryCount): self
    {
        $this->entryCount = $entryCount;

        return $this;
    }

    private function updateEntryCounts(): self
    {
        $this->setEntryCount(
            $this->entries->count()
        );

        return $this;
    }

    public function updateEntryCount(): self
    {
        $this->entryCount = $this->entries->count();

        return $this;
    }


    public function getCommentCount(): ?int
    {
        return $this->commentCount;
    }

    public function setCommentCount(int $commentCount): self
    {
        $this->commentCount = $commentCount;

        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->entries->add($post);
            $post->setMagazine($this);
        }

        $this->updatePostCounts();

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getMagazine() === $this) {
                $post->setMagazine(null);
            }
        }

        $this->updatePostCounts();

        return $this;
    }


    public function setPostCount(int $postCount): self
    {
        $this->postCount = $postCount;

        return $this;
    }

    private function updatePostCounts(): self
    {
        $this->setPostCount(
            $this->posts->count()
        );

        return $this;
    }

    public function updatePostCount(): self
    {
        $this->postCount = $this->posts->count();

        return $this;
    }

    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function isSubscribed(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return $this->subscriptions->matching($criteria)->count() > 0;
    }

    public function subscribe(User $user): self
    {
        if (!$this->isSubscribed($user)) {
            $this->subscriptions->add($sub = new MagazineSubscription($user, $this));
            $sub->setMagazine($this);
        }

        $this->updateSubscriptionsCount();

        return $this;
    }

    public function unsubscribe(User $user): void
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        $subscription = $this->subscriptions->matching($criteria)->first();

        if ($this->subscriptions->removeElement($subscription)) {
            if ($subscription->getMagazine() === $this) {
                $subscription->setMagazine(null);
            }
        }

        $this->updateSubscriptionsCount();
    }

    public function getSubscriptionsCount(): int
    {
        return $this->subscriptionsCount;
    }

    private function updateSubscriptionsCount(): self
    {
        $this->subscriptionsCount = $this->subscriptions->count();

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

    public function getBans(): Collection
    {
        return $this->bans;
    }

    public function isBanned(User $user): bool
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->gt('expiredAt', new \DateTime()))
            ->orWhere(Criteria::expr()->isNull('expiredAt'))
            ->andWhere(Criteria::expr()->eq('user', $user));

        return $this->bans->matching($criteria)->count() > 0;
    }

    public function addBan(User $user, User $bannedBy, ?string $reason, ?\DateTimeInterface $expiredAt): self
    {
        $ban = $this->isBanned($user);
        if (!$ban) {
            $this->bans->add($ban = new MagazineBan($this, $user, $bannedBy, $reason, $expiredAt));
            $ban->setMagazine($this);
        }

        return $this;
    }

    public function removeBan(MagazineBan $ban): self
    {
        if ($this->bans->removeElement($ban)) {
            if ($ban->getMagazine() === $this) {
                $ban->setMagazine(null);
            }
        }

        return $this;
    }

    public function unban(User $user)
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->gt('expiredAt', new \DateTime()))
            ->orWhere(Criteria::expr()->isNull('expiredAt'))
            ->andWhere(Criteria::expr()->eq('user', $user));

        /**
         * @var MagazineBan $ban
         */
        $ban = $this->bans->matching($criteria)->first();
        $ban->setExpiredAt(new \DateTime('+10 seconds'));

        return $this;
    }

    public function __sleep()
    {
        return [];
    }
}
