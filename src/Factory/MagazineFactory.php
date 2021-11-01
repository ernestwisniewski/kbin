<?php declare(strict_types = 1);

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
            $dto->isAdult,
            $dto->cover
        );
    }

    public function createDto(Magazine $magazine): MagazineDto
    {
        $dto                     = new MagazineDto();
        $dto->user               = $magazine->getOwner();
        $dto->cover              = $magazine->cover;
        $dto->name               = $magazine->name;
        $dto->title              = $magazine->title;
        $dto->description        = $magazine->description;
        $dto->rules              = $magazine->rules;
        $dto->subscriptionsCount = $magazine->subscriptionsCount;
        $dto->entryCount         = $magazine->entryCount;
        $dto->entryCommentCount  = $magazine->entryCommentCount;
        $dto->postCount          = $magazine->postCount;
        $dto->postCommentCount   = $magazine->postCommentCount;
        $dto->isAdult            = $magazine->isAdult;
        $dto->badges             = $magazine->badges;
        $dto->setId($magazine->getId());

        return $dto;
    }
}
