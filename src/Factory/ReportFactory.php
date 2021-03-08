<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\EntryDto;
use App\DTO\ReportDto;
use App\Entity\Contracts\ReportInterface;
use App\Entity\Entry;
use App\Entity\Report;
use App\Entity\User;

class ReportFactory
{
    public function createFromDto(ReportDto $reportDto, User $reporting): Report
    {
        $className = $reportDto->getReportClassName();

        return new $className($reporting, $reportDto->getSubject()->getUser(), $reportDto->getSubject(), $reportDto->getReason());
    }

//    public function createDto(Entry $entry): ReportDto
//    {
//
//    }
}
