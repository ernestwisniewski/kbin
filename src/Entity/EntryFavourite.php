<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EntryFavourite extends Favourite
{
    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="favourites")
     */
    public ?Entry $entry;

    public function __construct(User $user, Entry $entry)
    {
        parent::__construct($user);

        $this->magazine = $entry->magazine;
        $this->entry    = $entry;
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
