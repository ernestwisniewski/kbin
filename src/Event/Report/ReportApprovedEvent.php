<?php

declare(strict_types=1);

namespace App\Event\Report;

use App\Entity\Report;

class ReportApprovedEvent
{
    public function __construct(public Report $report)
    {
    }
}
