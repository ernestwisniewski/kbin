<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Site
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string")
     */
    public string $title;
    /**
     * @ORM\Column(type="boolean")
     */
    public bool $enabled;
    /**
     * @ORM\Column(type="boolean")
     */
    public bool $registrationOpen;

    public function getId(): ?int
    {
        return $this->id;
    }
}
