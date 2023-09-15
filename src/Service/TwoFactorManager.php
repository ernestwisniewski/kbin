<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class TwoFactorManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return string[]
     */
    public function createBackupCodes(User $user): array
    {
        $codes = $this->generateCodes();

        $user->setBackupCodes($codes);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $codes;
    }

    public function remove2FA(User $user): void
    {
        $user->setTotpSecret(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function generateCodes(): array
    {
        return array_map(
            fn () => substr(str_shuffle((string) hexdec(bin2hex(random_bytes(6)))), 0, 8),
            range(0, 9),
        );
    }
}
