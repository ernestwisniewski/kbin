<?php

namespace App\Entity;

use App\Repository\EntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntryRepository::class)
 */
class Entry
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
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $body = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url = null;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="entries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Magazine;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="entries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;
    /**
     * @var Magazine
     */
    private $magazine;

    public function __construct(string $title, ?string $body, ?string $url, Magazine $magazine, User $user) {

        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->magazine = $magazine;
        $this->user = $user;
        $user->addEntry($this);
    }
    public function getId(): ?int
    {
        return $this->id;
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

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

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

    public function getMagazine(): ?Magazine
    {
        return $this->Magazine;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
