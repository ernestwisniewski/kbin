<?php

namespace App\DTO;

use App\Entity\Magazine;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class EntryDto
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $body = null;

    /**
     * @var string|null
     */
    private $url = null;

    /**
     * @Assert\NotBlank()
     *
     * @var Magazine|null
     */
    private $magazine;

    /**
     * @var User
     */
    private $user;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     */
    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return Magazine|null
     */
    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    /**
     * @param Magazine $magazine
     */
    public function setMagazine(Magazine $magazine): void
    {
        $this->magazine = $magazine;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
