<?php declare(strict_types = 1);

namespace App\Event;

use App\Entity\Report;

class SubjectReportedEvent
{
    public function __construct(private Report $report)
    {
    }

    public function getReport(): Report
    {
        return $this->report;
    }
}
