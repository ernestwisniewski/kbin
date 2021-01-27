<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use App\Repository\MagazineRepository;
use Doctrine\ORM\Mapping as ORM;

/**
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
     * @ORM\OneToMany(targetEntity=Moderator::class, mappedBy="magazine", cascade={"persist"})
     */
    private Collection $moderators;

    /**
     * @ORM\OneToMany(targetEntity=Entry::class, mappedBy="magazine")
     */
    private Collection $entries;

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

    /**
     * @return Collection|Entry[]
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }
}
