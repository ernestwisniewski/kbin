<?php

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use Doctrine\ORM\EntityManagerInterface;

readonly class MagazineDelete
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine): void
    {
        $magazine->softDelete();

        $this->entityManager->flush();
    }
}
