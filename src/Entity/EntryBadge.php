<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EntryBadge
{
    /**
     * @ORM\ManyToOne(targetEntity="Badge", inversedBy="badges")
     */
    public Badge $badge;
    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="badges")
     */
    public Entry $entry;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(Entry $entry, Badge $badge)
    {

        $this->entry = $entry;
        $this->badge = $badge;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->badge->name;
    }
}
