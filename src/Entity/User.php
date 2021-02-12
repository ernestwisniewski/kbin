<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Criteria;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\CreatedAtTrait;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface
{
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
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private string $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $username;

    /**
     * @ORM\Column(type="integer")
     */
    private int $followersCount = 0;

    /**
     * @ORM\OneToMany(targetEntity=Moderator::class, mappedBy="user")
     */
    private Collection $moderatorTokens;

    /**
     * @ORM\OneToMany(targetEntity=Entry::class, mappedBy="user")
     */
    private Collection $entries;

    /**
     * @ORM\OneToMany(targetEntity="EntryVote", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private Collection $entryVotes;

    /**
     * @ORM\OneToMany(targetEntity=EntryComment::class, mappedBy="user")
     */
    private Collection $entryComments;

    /**
     * @ORM\OneToMany(targetEntity="EntryCommentVote", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private Collection $entryCommentVotes;

    /**
     * @ORM\OneToMany(targetEntity=MagazineSubscription::class, mappedBy="user", orphanRemoval=true)
     */
    private Collection $subscriptions;

    /**
     * @ORM\OneToMany(targetEntity=UserFollow::class, mappedBy="follower", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private Collection $follows;

    /**
     * @ORM\OneToMany(targetEntity=UserFollow::class, mappedBy="following", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private Collection $followers;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isVerified = true;//@todo

    public function __construct($email, $username, $password)
    {
        $this->email    = $email;
        $this->password = $password;
        $this->username = $username;

        $this->moderatorTokens   = new ArrayCollection();
        $this->entries           = new ArrayCollection();
        $this->entryVotes        = new ArrayCollection();
        $this->entryComments     = new ArrayCollection();
        $this->entryCommentVotes = new ArrayCollection();

        $this->createdAtTraitConstruct();
        $this->subscriptions = new ArrayCollection();
        $this->follows       = new ArrayCollection();
        $this->followers     = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
//         $this->plainPassword = null;
    }

    public function getModeratorTokens(): Collection
    {
        return $this->moderatorTokens;
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(Entry $entry): self
    {
        if ($entry->getUser() !== $this) {
            throw new \DomainException('Entry must belong to user');
        }

        if (!$this->entries->contains($entry)) {
            $this->entries[] = $entry;
        }

        return $this;
    }

    public function getEntryVotes(): Collection
    {
        return $this->entryVotes;
    }


    public function getEntryComments(): Collection
    {
        return $this->entryComments;
    }

    public function addEntryComment(EntryComment $comment): self
    {
        if (!$this->entryComments->contains($comment)) {
            $this->entryComments[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|MagazineSubscription[]
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(MagazineSubscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setUser($this);
        }

        return $this;
    }

    public function removeSubscription(MagazineSubscription $subscription): self
    {
        if ($this->subscriptions->removeElement($subscription)) {
            if ($subscription->getUser() === $this) {
                $subscription->setUser(null);
            }
        }

        return $this;
    }

    public function isFollowing(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('following', $user));

        return \count($this->follows->matching($criteria)) > 0;
    }

    public function isFollower(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('follower', $this));

        return \count($user->followers->matching($criteria)) > 0;
    }

    public function follow(User $following): self
    {
        if (!$this->isFollowing($following)) {
            $this->followers->add($follower = new UserFollow($this, $following));

            if (!$following->followers->contains($follower)) {
                $following->followers->add($follower);
            }
        }

        $this->updateFollowCounts($following);

        return $this;
    }

    public function unfollow(User $following): void
    {
        $followingUser = $following;

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('following', $following));

        /**
         * @var $following UserFollow
         */
        $following = $this->follows->matching($criteria)->first();

        if ($this->follows->removeElement($following)) {
            if ($following->getFollower() === $this) {
                $following->setFollower(null);
                $followingUser->followers->removeElement($following);
            }
        }

        $this->updateFollowCounts($followingUser);
    }

    public function getFollows()
    {
        return $this->follows;
    }

    public function getFollowers()
    {
        return $this->followers;
    }

    public function getFollowersCount(): int
    {
        return $this->followersCount;
    }

    public function updateFollowCounts(User $following)
    {
        $following->followersCount = $following->followers->count();
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
