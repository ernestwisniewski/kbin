<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Kbin\Magazine\DTO\MagazineDto;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

readonly class MagazineEdit
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, MagazineDto $dto): Magazine
    {
        Assert::same($magazine->name, $dto->name);

        $magazine->title = $dto->title;
        $magazine->description = $dto->description;
        $magazine->rules = $dto->rules;
        $magazine->isAdult = $dto->isAdult;

        $this->entityManager->flush();

        return $magazine;
    }
}
