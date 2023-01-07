<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class EntryFavourite extends Favourite
{
    #[ManyToOne(targetEntity: Entry::class, inversedBy: 'favourites')]
    #[JoinColumn(nullable: true)]
    public ?Entry $entry = null;

    public function __construct(User $user, Entry $entry)
    {
        parent::__construct($user);

        $this->magazine = $entry->magazine;
        $this->entry = $entry;
    }

    public function getSubject(): Entry
    {
        return $this->entry;
    }

    public function clearSubject(): Favourite
    {
        $this->entry = null;

        return $this;
    }

    public function getType(): string
    {
        return 'entry';
    }
}
