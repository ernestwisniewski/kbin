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
    private Entry $entry;

    public function __construct(User $reporting, User $reported, Entry $entry)
    {
        parent::__construct($reporting, $reported, $entry->getMagazine());

        $this->entry = $entry;
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }

    public function getType(): string
    {
        return 'entry';
    }
}
