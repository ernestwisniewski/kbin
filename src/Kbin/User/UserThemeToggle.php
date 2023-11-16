<?php

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserThemeToggle
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user): void
    {
        $user->toggleTheme();

        $this->entityManager->flush();
    }
}
