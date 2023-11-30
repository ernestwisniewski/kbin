<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: CategoryRepository::class)]
#[Table]
#[UniqueConstraint(name: 'category_name_user_idx', columns: ['name', 'user_id'])]
class Category
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: User::class, inversedBy: 'categories')]
    #[JoinColumn(nullable: false)]
    public User $user;
    #[Column]
    public string $name;
    #[Column]
    public string $slug;
    #[Column(type: 'text', nullable: true)]
    public ?string $description = null;
    #[Column(type: 'boolean', options: ['default' => false])]
    public bool $isPrivate = false;
    #[Column(type: 'boolean', options: ['default' => false])]
    public bool $isOfficial = false;
    #[Column(type: 'integer', options: ['default' => 0])]
    public int $magazinesCount = 0;
    #[Column(type: 'integer', options: ['default' => 0])]
    public int $subscriptionsCount = 0;
    #[OneToMany(mappedBy: 'category', targetEntity: CategoryMagazine::class, cascade: [
        'persist',
        'remove',
    ], orphanRemoval: true)]
    public Collection $magazines;
    #[OneToMany(mappedBy: 'category', targetEntity: CategorySubscription::class, cascade: [
        'persist',
        'remove',
    ], orphanRemoval: true)]
    public Collection $subscriptions;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct()
    {
        $this->createdAtTraitConstruct();
        $this->subscriptions = new ArrayCollection();
        $this->magazines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function addMagazine(Magazine $magazine): self
    {
        if (!$this->magazines->contains($magazine)) {
            $this->magazines->add(new CategoryMagazine($this, $magazine));
        }

        $this->updateCounts();

        return $this;
    }

    private function updateCounts(): void
    {
        $this->magazinesCount = $this->magazines->count();
        $this->subscriptionsCount = $this->subscriptions->count();
    }

    public function getMagazines(): Collection
    {
        return new ArrayCollection(
            $this->magazines->map(fn (CategoryMagazine $categoryMagazine) => $categoryMagazine->magazine)->toArray()
        );
    }

    public function isSubscribed(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return $this->subscriptions->matching($criteria)->count() > 0;
    }

    public function unsubscribe(User $user): void
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        $subscription = $this->subscriptions->matching($criteria)->first();

        if ($this->subscriptions->removeElement($subscription)) {
            if ($subscription->category === $this) {
                $subscription->category = null;
            }
        }

        $this->updateCounts();
    }

    public function subscribe(User $user): self
    {
        if (!$this->isSubscribed($user)) {
            $this->subscriptions->add($sub = new CategorySubscription($user, $this));
        }

        $this->updateCounts();

        return $this;
    }
}
