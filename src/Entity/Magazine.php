<?php declare(strict_types = 1);

namespace App\Entity;

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
class Magazine
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
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $rules = null;

    /**
     * @ORM\OneToMany(targetEntity=Moderator::class, mappedBy="magazine", cascade={"persist"})
     */
    private Collection $moderators;

    /**
     * @ORM\OneToMany(targetEntity=Entry::class, mappedBy="magazine")
     */
    private Collection $entries;

    /**
     * @ORM\Column(type="integer")
     */
    private int $entryCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $commentCount = 0;

    public function __construct(string $name, string $title, User $user)
    {
        $this->name       = $name;
        $this->title      = $title;
        $this->moderators = new ArrayCollection();
        $this->entries    = new ArrayCollection();

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

    public function getModerators(): Collection
    {
        return $this->moderators;
    }

    public function addModerator(Moderator $moderator): self
    {
        if (!$this->moderators->contains($moderator)) {
            $this->moderators[] = $moderator;
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
            $this->entries[] = $entry;
            $entry->setMagazine($this);
        }

        $this->updateCounts();

        return $this;
    }

    public function removeEntry(Entry $entry): self
    {
        if ($this->entries->removeElement($entry)) {
            if ($entry->getMagazine() === $this) {
                $entry->setMagazine(null);
            }
        }

        $this->updateCounts();

        return $this;
    }

    private function updateCounts(): self
    {
        $this->setEntryCount(
            $this->entries->count()
        );

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

    public function getCommentCount(): ?int
    {
        return $this->commentCount;
    }

    public function setCommentCount(int $commentCount): self
    {
        $this->commentCount = $commentCount;

        return $this;
    }

    public function updateEntryCount(): self
    {
        $this->entryCount = $this->entries->count();

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
}
