<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
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

    public const THEME_LIGHT = 'light';
    public const THEME_DARK = 'dark';
    public const THEME_AUTO = 'auto';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Image", cascade={"persist"})
     */
    private ?Image $avatar = null;

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
     * @ORM\Column(type="string", length=35)
     */
    private string $username;

    /**
     * @ORM\Column(type="integer")
     */
    private int $followersCount = 0;

    /**
     * @ORM\Column(type="string", options={"default": User::THEME_LIGHT})
     */
    private string $theme = self::THEME_LIGHT;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isVerified = false;//@todo

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
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="user")
     */
    private Collection $posts;

    /**
     * @ORM\OneToMany(targetEntity="PostVote", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private Collection $postVotes;

    /**
     * @ORM\OneToMany(targetEntity=PostComment::class, mappedBy="user")
     */
    private Collection $postComments;

    /**
     * @ORM\OneToMany(targetEntity="PostCommentVote", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private Collection $postCommentVotes;

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
     * @ORM\OneToMany(targetEntity=UserBlock::class, mappedBy="blocker", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private Collection $blocks;

    /**
     * @ORM\OneToMany(targetEntity=UserBlock::class, mappedBy="blocked", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private ?Collection $blockers;

    /**
     * @ORM\OneToMany(targetEntity=MagazineBlock::class, mappedBy="user", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private Collection $blockedMagazines;

    /**
     * @ORM\OneToMany(targetEntity="Report", mappedBy="reporting", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\OrderBy({"id": "DESC"})
     */
    private Collection $reports;

    /**
     * @ORM\OneToMany(targetEntity="Report", mappedBy="reported", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\OrderBy({"id": "DESC"})
     */
    private Collection $violations;

    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="user", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\OrderBy({"createdAt": "DESC"})
     */
    private Collection $notifications;

    public function __construct($email, $username, $password)
    {
        $this->email             = $email;
        $this->password          = $password;
        $this->username          = $username;
        $this->moderatorTokens   = new ArrayCollection();
        $this->entries           = new ArrayCollection();
        $this->entryVotes        = new ArrayCollection();
        $this->entryComments     = new ArrayCollection();
        $this->entryCommentVotes = new ArrayCollection();
        $this->posts             = new ArrayCollection();
        $this->postVotes         = new ArrayCollection();
        $this->postComments      = new ArrayCollection();
        $this->postCommentVotes  = new ArrayCollection();
        $this->subscriptions     = new ArrayCollection();
        $this->follows           = new ArrayCollection();
        $this->followers         = new ArrayCollection();
        $this->blocks            = new ArrayCollection();
        $this->blockers          = new ArrayCollection();
        $this->blockedMagazines  = new ArrayCollection();
        $this->reports           = new ArrayCollection();
        $this->violations        = new ArrayCollection();
        $this->notifications     = new ArrayCollection();

        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }


    public function getAvatar(): ?Image
    {
        return $this->avatar;
    }

    public function setAvatar(?Image $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
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

    public function getModeratedMagazines(): Collection
    {
        $this->getModeratorTokens()->get(-1);

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isConfirmed', true));

        return $this->getModeratorTokens()->matching($criteria);
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
            $this->entries->add($entry);
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
            $this->entryComments->add($comment);
            $comment->setUser($this);
        }

        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if ($post->getUser() !== $this) {
            throw new \DomainException('Post must belong to user');
        }

        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
        }

        return $this;
    }

    public function getPostComments(): Collection
    {
        return $this->postComments;
    }

    public function addPostComment(PostComment $comment): self
    {
        if (!$this->entryComments->contains($comment)) {
            $this->entryComments->add($comment);
            $comment->setUser($this);
        }

        return $this;
    }

    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(MagazineSubscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
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

        return $this->follows->matching($criteria)->count() > 0;
    }

    public function isFollower(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('follower', $this));

        return $user->followers->matching($criteria)->count() > 0;
    }

    public function follow(User $following): self
    {
        $this->unblock($following);

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

    public function getFollows(): Collection
    {
        return $this->follows;
    }

    public function getFollowers(): Collection
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

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function toggleTheme(): self
    {
        $this->theme = $this->theme === self::THEME_LIGHT ? self::THEME_DARK : self::THEME_LIGHT;

        return $this;
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

    public function isBlocked(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('blocked', $user));

        return $this->blocks->matching($criteria)->count() > 0;
    }

    public function isBlocker(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('blocker', $user));

        return $user->blockers->matching($criteria)->count() > 0;
    }

    public function block(User $blocked): self
    {
        if (!$this->isBlocked($blocked)) {
            $this->blocks->add($userBlock = new UserBlock($this, $blocked));

            if (!$blocked->blockers->contains($userBlock)) {
                $blocked->blockers->add($userBlock);
            }
        }

        return $this;
    }

    public function unblock(User $blocked): void
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('blocked', $blocked));

        /**
         * @var $userBlock UserBlock
         */
        $userBlock = $this->blocks->matching($criteria)->first();

        if ($this->blocks->removeElement($userBlock)) {
            if ($userBlock->getBlocker() === $this) {
                $userBlock->setBlocker(null);
                $blocked->blockers->removeElement($this);
            }
        }
    }

    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function getBlockers(): Collection
    {
        return $this->blockers;
    }

    public function isBlockedMagazine(Magazine $magazine): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('magazine', $magazine));

        return $this->blockedMagazines->matching($criteria)->count() > 0;
    }

    public function blockMagazine(Magazine $magazine): self
    {
        if (!$this->isBlockedMagazine($magazine)) {
            $this->blockedMagazines->add($magazineBlock = new MagazineBlock($this, $magazine));
        }

        return $this;
    }

    public function unblockMagazine(Magazine $magazine): void
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('magazine', $magazine));

        /**
         * @var $magazineBlock MagazineBlock
         */
        $magazineBlock = $this->blockedMagazines->matching($criteria)->first();

        if ($this->blockedMagazines->removeElement($magazineBlock)) {
            if ($magazineBlock->getUser() === $this) {
                $magazineBlock->setMagazine(null);
                $this->blockedMagazines->removeElement($magazineBlock);
            }
        }
    }
}
