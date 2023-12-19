<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ActivityPubActorInterface;
use App\Entity\Contracts\ApiResourceInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Traits\ActivityPubActorTrait;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\VisibilityTrait;
use App\Repository\MagazineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: MagazineRepository::class)]
#[Index(columns: ['visibility', 'is_adult'], name: 'magazine_visibility_adult_idx')]
#[Index(columns: ['visibility'], name: 'magazine_visibility_idx')]
#[Index(columns: ['is_adult'], name: 'magazine_adult_idx')]
#[Index(columns: ['ap_id'], name: 'magazine_ap_id_idx')]
#[Index(columns: ['ap_profile_id'], name: 'magazine_ap_profile_id_idx')]
#[UniqueConstraint(name: 'magazine_name_idx', columns: ['name'])]
#[Cache(usage: 'NONSTRICT_READ_WRITE')]
class Magazine implements VisibilityInterface, ActivityPubActorInterface, ApiResourceInterface
{
    use ActivityPubActorTrait;
    use VisibilityTrait;
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    public const MAX_DESCRIPTION_LENGTH = 10000;
    public const MAX_RULES_LENGTH = 10000;

    #[ManyToOne(targetEntity: Image::class, cascade: ['persist'])]
    #[JoinColumn]
    public ?Image $icon = null;
    #[Column(type: 'string')]
    public string $name;
    #[Column(type: 'string')]
    public ?string $title;
    #[Column(type: 'text', length: self::MAX_DESCRIPTION_LENGTH, nullable: true)]
    public ?string $description = null;
    #[Column(type: 'text', length: self::MAX_RULES_LENGTH, nullable: true)]
    public ?string $rules = null;
    #[Column(type: 'integer')]
    public int $subscriptionsCount = 0;
    #[Column(type: 'integer')]
    public int $entryCount = 0;
    #[Column(type: 'integer')]
    public int $entryCommentCount = 0;
    #[Column(type: 'integer')]
    public int $postCount = 0;
    #[Column(type: 'integer')]
    public int $postCommentCount = 0;
    #[Column(type: 'boolean')]
    public bool $isAdult = false;
    #[Column(type: 'text', nullable: true)]
    public ?string $customCss = null;
    #[Column(type: 'datetimetz', nullable: true)]
    public ?\DateTime $lastActive = null;
    #[Column(type: 'datetimetz', nullable: true)]
    public ?\DateTime $markedForDeletionAt = null;
    #[Column(type: 'json', nullable: true, options: ['jsonb' => true])]
    public ?array $tags = null;
    #[OneToMany(mappedBy: 'magazine', targetEntity: Moderator::class, cascade: ['persist'])]
    public Collection $moderators;
    #[OneToMany(mappedBy: 'magazine', targetEntity: MagazineOwnershipRequest::class, cascade: ['persist'])]
    public Collection $ownershipRequests;
    #[OneToMany(mappedBy: 'magazine', targetEntity: ModeratorRequest::class, cascade: ['persist'])]
    public Collection $moderatorRequests;
    #[OneToMany(mappedBy: 'magazine', targetEntity: Entry::class)]
    public Collection $entries;
    #[OneToMany(mappedBy: 'magazine', targetEntity: Post::class)]
    public Collection $posts;
    #[OneToMany(mappedBy: 'magazine', targetEntity: MagazineSubscription::class, cascade: [
        'persist',
        'remove',
    ], orphanRemoval: true)]
    public Collection $subscriptions;
    #[OneToMany(mappedBy: 'magazine', targetEntity: MagazineBan::class, cascade: [
        'persist',
        'remove',
    ], orphanRemoval: true)]
    public Collection $bans;
    #[OneToMany(mappedBy: 'magazine', targetEntity: Report::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    #[OrderBy(['createdAt' => 'DESC'])]
    public Collection $reports;
    #[OneToMany(mappedBy: 'magazine', targetEntity: Badge::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    #[OrderBy(['id' => 'DESC'])]
    public Collection $badges;
    #[OneToMany(mappedBy: 'magazine', targetEntity: MagazineLog::class, cascade: [
        'persist',
        'remove',
    ], fetch: 'EXTRA_LAZY')]
    #[OrderBy(['createdAt' => 'DESC'])]
    public Collection $logs;
    #[OneToMany(mappedBy: 'magazine', targetEntity: Award::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    #[OrderBy(['createdAt' => 'DESC'])]
    public Collection $awards;
    #[OneToMany(mappedBy: 'magazine', targetEntity: CategoryMagazine::class, cascade: [
        'persist',
        'remove',
    ], orphanRemoval: true)]
    public Collection $categories;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(
        string $name,
        string $title,
        User $user,
        ?string $description,
        ?string $rules,
        bool $isAdult,
        ?Image $icon
    ) {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
        $this->rules = $rules;
        $this->isAdult = $isAdult;
        $this->icon = $icon;
        $this->moderators = new ArrayCollection();
        $this->ownershipRequests = new ArrayCollection();
        $this->moderatorRequests = new ArrayCollection();
        $this->entries = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->bans = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->badges = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->awards = new ArrayCollection();
        $this->categories = new ArrayCollection();

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

    public function isAbandoned(): bool
    {
        return $this->getOwner()->lastActive < new \DateTime('-1 month');
    }

    public function getOwnerModerator(): Moderator
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('isOwner', true));

        return $this->moderators->matching($criteria)->first();
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

    public function updatePostCounts(): self
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('visibility', VisibilityInterface::VISIBILITY_VISIBLE));

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
        $this->visibility = VisibilityInterface::VISIBILITY_SOFT_DELETED;
    }

    public function trash(): void
    {
        $this->markedForDeletionAt = new \DateTime();
        $this->visibility = VisibilityInterface::VISIBILITY_TRASHED;
    }

    public function restore(): void
    {
        $this->markedForDeletionAt = null;
        $this->visibility = VisibilityInterface::VISIBILITY_VISIBLE;
    }

    public function addBan(User $user, User $bannedBy, ?string $reason, ?\DateTimeInterface $expiredAt): ?MagazineBan
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
            ->andWhere(Criteria::expr()->gt('expiredAt', new \DateTime()))
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
            ->andWhere(Criteria::expr()->gt('expiredAt', new \DateTime()))
            ->orWhere(Criteria::expr()->isNull('expiredAt'))
            ->andWhere(Criteria::expr()->eq('user', $user));

        /**
         * @var MagazineBan $ban
         */
        $ban = $this->bans->matching($criteria)->first();
        $ban->expiredAt = new \DateTime('+10 seconds');

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

    public function getApName(): string
    {
        return $this->name;
    }
}
