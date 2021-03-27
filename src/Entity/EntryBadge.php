<?php

namespace App\Entity;

use App\Repository\EntryBadgeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntryBadgeRepository::class)
 */
class EntryBadge
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Badge")
     */
    private Badge $badge;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="badges")
     */
    private Entry $entry;

    public function __construct() {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBadge(): ?Badge
    {
        return $this->badge;
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }
}
