<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EntryReport extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="reports")
     */
    public ?Entry $entry;

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
