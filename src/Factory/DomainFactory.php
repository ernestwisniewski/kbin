<?php declare(strict_types = 1);

namespace App\Factory;

use App\DTO\DomainDto;
use App\Entity\Domain;

class DomainFactory
{
    public function createDto(Domain $domin): DomainDto
    {
        return (new DomainDto())->create(
            $domin->name,
            $domin->entryCount,
            $domin->getId(),
        );
    }
}
