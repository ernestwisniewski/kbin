<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\MagazineDto;
use App\Entity\Magazine;
use App\Entity\User;

class MagazineFactory
{
    public function createFromDto(MagazineDto $dto, User $user): Magazine
    {
        return new Magazine(
            $dto->name,
            $dto->title,
            $user,
            $dto->description,
            $dto->rules,
            $dto->isAdult
        );
    }

    public function createDto(Magazine $magazine): MagazineDto
    {
        return (new MagazineDto())->create(
            $magazine->name,
            $magazine->title,
            $magazine->description,
            $magazine->rules,
            $magazine->isAdult,
            $magazine->getId()
        );
    }
}
