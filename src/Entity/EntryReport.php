<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ReportInterface;
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
    private ?Entry $entry;

    public function __construct(User $reporting, User $reported, Entry $entry, ?string $reason = null)
    {
        parent::__construct($reporting, $reported, $entry->getMagazine(), $reason);

        $this->entry = $entry;
    }

    public function getEntry(): Entry
    {
        return $this->entry;
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
