<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\DomainInterface;
use App\Repository\DomainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="domain_name_idx", columns={"name"}),
 * })
 * @ORM\Entity(repositoryClass=DomainRepository::class)
 */
class Domain
{
    /**
     * @ORM\OneToMany(targetEntity=Entry::class, mappedBy="domain")
     */
    public Collection $entries;
    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $name;
    /**
     * @ORM\Column(type="integer")
     */
    public int $entryCount = 0;
    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    public int $subscriptionsCount = 0;
    /**
     * @ORM\OneToMany(targetEntity=DomainSubscription::class, mappedBy="domain", orphanRemoval=true, cascade={"persist", "remove"})
     */
    public Collection $subscriptions;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(DomainInterface $entry, string $name)
    {
        $this->name          = $name;
        $this->entries       = new ArrayCollection();
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

    public function updateCounts()
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

    public function __sleep()
    {
        return [];
    }
}
