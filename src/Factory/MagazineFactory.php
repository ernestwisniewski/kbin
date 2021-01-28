<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\MagazineDto;
use App\Entity\Magazine;
use App\Entity\User;

class MagazineFactory
{
    public function createFromDto(MagazineDto $magazineDto, User $user): Magazine
    {
        return new Magazine(
            $magazineDto->getName(),
            $magazineDto->getTitle(),
            $user
        );
    }

    public function createDto(Magazine $magazine): MagazineDto
    {
        return (new MagazineDto())->create(
            $magazine->getName(),
            $magazine->getTitle(),
            $magazine->getId()
        );
    }
}
