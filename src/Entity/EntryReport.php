<?php

namespace App\Entity;

use App\Repository\EntryReportRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntryReportRepository::class)
 */
class EntryReport extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="reports")
     */
    private Entry $subject;

    public function __construct(User $reporting, User $reported, Entry $entry, ?string $reason = null)
    {
        parent::__construct($reporting, $reported, $entry->getMagazine(), $reason);

        $this->subject = $entry;
    }

    public function getSubject(): Entry
    {
        return $this->subject;
    }

    public function getType(): string
    {
        return 'entry';
    }
}
