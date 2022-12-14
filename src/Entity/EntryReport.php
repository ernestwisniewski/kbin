<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class EntryReport extends Report
{
    #[ManyToOne(targetEntity: Entry::class, inversedBy: 'reports')]
    #[JoinColumn(nullable: true)]
    public ?Entry $entry = null;

    public function __construct(User $reporting, Entry $entry, ?string $reason = null)
    {
        parent::__construct($reporting, $entry->user, $entry->magazine, $reason);

        $this->entry = $entry;
    }

    public function getSubject(): Entry
    {
        return $this->entry;
    }

    public function clearSubject(): Report
    {
        $this->entry = null;

        return $this;
    }

    public function getType(): string
    {
        return 'entry';
    }
}
