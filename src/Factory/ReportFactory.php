<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\ReportDto;
use App\Entity\Report;
use App\Entity\User;

class ReportFactory
{
    public function createFromDto(ReportDto $dto, User $reporting): Report
    {
        $className = get_class($dto->getSubject()).'Report';

        return new $className($dto->getSubject()->user, $dto->getSubject(), $dto->reason);
    }
}
