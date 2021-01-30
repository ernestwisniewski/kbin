<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Contracts\Commentable;
use App\Entity\Traits\VotableTrait;
use App\Repository\EntryRepository;
use App\Entity\Contracts\Votable;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity(repositoryClass=EntryRepository::class)
 */
class Entry implements Votable, Commentable
{
    use VotableTrait;
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    const ENTRY_TYPE_ARTICLE = 'artykul';
    const ENTRY_TYPE_LINK = 'link';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="entries")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="entries")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private ?Magazine $magazine;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $url = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $body = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $embed = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $commentCount = 0;

    /**
     * @ORM\OneToMany(targetEntity=EntryComment::class, mappedBy="entry")
     */
    private Collection $comments;

    /**
     * @ORM\OneToMany(targetEntity=EntryVote::class, mappedBy="entry", cascade={"persist"})
     */
    private Collection $votes;

    public function __construct(string $title, ?string $url, ?string $body, Magazine $magazine, User $user)
    {
        $this->title    = $title;
        $this->url      = $url;
        $this->body     = $body;
        $this->magazine = $magazine;
        $this->user     = $user;
        $this->comments = new ArrayCollection();
        $this->votes    = new ArrayCollection();

        $user->addEntry($this);

        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getMagazine(): Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(?Magazine $magazine): self
    {
        $this->magazine = $magazine;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }


    public function getEmbed(): ?string
    {
        return $this->embed;
    }

    public function setEmbed(?string $embed): self
    {
        $this->embed = $embed;

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

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(EntryComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setEntry($this);
        }

        $this->updateCounts();

        return $this;
    }

    public function removeComment(EntryComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getEntry() === $this) {
                $comment->setEntry(null);
            }
        }

        $this->updateCounts();

        return $this;
    }

    private function updateCounts(): self
    {
        $this->setCommentCount(
            $this->getComments()->count()
        );

        return $this;
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setEntry($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryVote::class);

        if ($this->votes->removeElement($vote)) {
            if ($vote->getEntry() === $this) {
                $vote->setEntry(null);
            }
        }

        return $this;
    }
}
