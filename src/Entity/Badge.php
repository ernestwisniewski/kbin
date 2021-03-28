<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\BadgeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BadgeRepository::class)
 */
class Badge
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="badges")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private Magazine $magazine;

    /**
     * @ORM\OneToMany(targetEntity="EntryBadge", mappedBy="badge", cascade={"remove"}, orphanRemoval=true)
     */
    protected Collection $badges;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    public function __construct(Magazine $magazine, string $name)
    {
        $this->magazine = $magazine;
        $this->name     = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
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

    public function getBadges(): Collection
    {
        return $this->badges;
    }

    public function countBadges(): int
    {
        return $this->badges->count();
    }
}
