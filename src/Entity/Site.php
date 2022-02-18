<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Site
{
    /**
     * @ORM\Column(type="string")
     */
    public string $domain;
    /**
     * @ORM\Column(type="string")
     */
    public string $title;
    /**
     * @ORM\Column(type="text", nullable=true, options={"default" : null})
     */
    public ?string $description;
    /**
     * @ORM\Column(type="text", nullable=true, options={"default" : null})
     */
    public ?string $terms = null;
    /**
     * @ORM\Column(type="text", nullable=true, options={"default" : null})
     */
    public ?string $privacyPolicy = null;
    /**
     * @ORM\Column(type="boolean")
     */
    public bool $enabled;
    /**
     * @ORM\Column(type="boolean")
     */
    public bool $registrationOpen;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
