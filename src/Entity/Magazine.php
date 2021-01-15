<?php

namespace App\Entity;

use App\Repository\MagazineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MagazineRepository::class)
 */
class Magazine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=Moderator::class, mappedBy="magazine", cascade={"persist"})
     */
    private $moderators;

    public function __construct(string $name, string $title, User $user)
    {
        $this->name = $name;
        $this->title = $title;
        $this->moderators = new ArrayCollection();

        $this->addModerator(new Moderator($this, $user, true));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|Moderator[]
     */
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

    public function removeModerator(Moderator $moderator): self
    {
        if ($this->moderators->removeElement($moderator)) {
            // set the owning side to null (unless already changed)
            if ($moderator->getMagazine() === $this) {
                $moderator->setMagazine(null);
            }
        }

        return $this;
    }
}
