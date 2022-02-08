<?php declare(strict_types = 1);

namespace App\Factory;

use App\DTO\ReportDto;
use App\Entity\Report;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ReportFactory
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createFromDto(ReportDto $dto): Report
    {
        $className = $this->entityManager->getClassMetadata(get_class($dto->getSubject()))->name.'Report';

        return new $className($dto->getSubject()->user, $dto->getSubject(), $dto->reason);
    }
}
