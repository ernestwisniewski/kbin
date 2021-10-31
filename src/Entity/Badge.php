<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Badge
{
    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="badges")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    public Magazine $magazine;
    /**
     * @ORM\OneToMany(targetEntity="EntryBadge", mappedBy="badge", cascade={"remove"}, orphanRemoval=true)
     */
    public Collection $badges;
    /**
     * @ORM\Column(type="string")
     */
    public ?string $name;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(Magazine $magazine, string $name)
    {
        $this->magazine = $magazine;
        $this->name     = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function countBadges(): int
    {
        return $this->badges->count();
    }
}
