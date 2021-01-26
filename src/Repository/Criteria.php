<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\Entry;
use App\Entity\User;

class Criteria
{
    private int $page = 1;
    private ?Magazine $magazine = null;
    private ?Entry $entry = null;
    private ?User $user = null;

    public function __construct(int $page)
    {
        $this->page = $page;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(Magazine $magazine): self
    {
        $this->magazine = $magazine;

        return $this;
    }

    public function getEntry(): ?Entry
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
