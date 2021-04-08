<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\ReportDto;
use App\Entity\Report;
use App\Entity\User;

class ReportFactory
{
    public function createFromDto(ReportDto $reportDto, User $reporting): Report
    {
        $className = get_class($reportDto->getSubject()).'Report';

        return new $className($reporting, $reportDto->getSubject()->user, $reportDto->getSubject(), $reportDto->reason);
    }

//    public function createDto(Entry $entry): ReportDto
//    {
//
//    }
}
