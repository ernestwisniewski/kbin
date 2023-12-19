<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\DomainInterface;
use App\Kbin\Domain\DomainShouldRatio;
use App\Repository\DomainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: DomainRepository::class)]
#[Table]
#[ORM\UniqueConstraint(name: 'domain_name_idx', columns: ['name'])]
#[Cache(usage: 'NONSTRICT_READ_WRITE')]
class Domain
{
    #[OneToMany(mappedBy: 'domain', targetEntity: Entry::class)]
    public Collection $entries;
    #[Column(type: 'string')]
    public string $name;
    #[Column(type: 'integer')]
    public int $entryCount = 0;
    #[Column(type: 'integer', options: ['default' => 0])]
    public int $subscriptionsCount = 0;
    #[OneToMany(mappedBy: 'domain', targetEntity: DomainSubscription::class, cascade: [
        'persist',
        'remove',
    ], orphanRemoval: true)]
    public Collection $subscriptions;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(DomainInterface $entry, string $name)
    {
        $this->name = $name;
        $this->entries = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();

        $this->addEntry($entry);
    }

    public function addEntry(DomainInterface $subject): self
    {
        if (!$this->entries->contains($subject)) {
            $this->entries->add($subject);
            $subject->setDomain($this);
        }

        $this->updateCounts();

        return $this;
    }

    public function updateCounts(): void
    {
        $this->entryCount = $this->entries->count();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function removeEntry(DomainInterface $subject): self
    {
        if ($this->entries->removeElement($subject)) {
            if ($subject->getDomain() === $this) {
                $subject->setDomain(null);
            }
        }

        $this->updateCounts();

        return $this;
    }

    public function subscribe(User $user): self
    {
        if (!$this->isSubscribed($user)) {
            $this->subscriptions->add($sub = new DomainSubscription($user, $this));
            $sub->domain = $this;
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
            if ($subscription->domain === $this) {
                $subscription->domain = null;
            }
        }

        $this->updateSubscriptionsCount();
    }

    public function shouldRatio(): bool
    {
        return DomainShouldRatio::check($this->name);
    }

    public function __sleep()
    {
        return [];
    }
}
