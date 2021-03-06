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

    public function __construct(User $reporting, Entry $comment) {
        parent::__construct($reporting);

        $this->entry = $comment;
    }

    public function getEntry(): Entry {
        return $this->entry;
    }

    public function getType(): string {
        return 'entry';
    }
}
