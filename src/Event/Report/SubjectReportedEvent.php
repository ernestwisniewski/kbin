<?php declare(strict_types = 1);

namespace App\Event\Report;

use App\Entity\Report;

class SubjectReportedEvent
{
    public function __construct(public Report $report)
    {
    }
}
