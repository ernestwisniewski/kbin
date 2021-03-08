<?php declare(strict_types = 1);

namespace App\Event;

use App\Entity\Report;

class SubjectReportedEvent
{
    protected Report $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function getReport(): Report
    {
        return $this->report;
    }
}
