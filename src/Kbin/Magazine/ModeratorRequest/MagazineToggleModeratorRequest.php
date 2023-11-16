<?php

declare(strict_types=1);

namespace App\Kbin\Magazine\ModeratorRequest;

use App\Entity\Magazine;
use App\Entity\ModeratorRequest;
use App\Entity\User;
use App\Repository\ModeratorRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class MagazineToggleModeratorRequest
{
    public function __construct(
        private ModeratorRequestRepository $moderatorRequestRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user): void
    {
        $request = $this->moderatorRequestRepository->findOneBy([
            'magazine' => $magazine,
            'user' => $user,
        ]);

        if ($request) {
            $this->entityManager->remove($request);
            $this->entityManager->flush();

            return;
        }

        $request = new ModeratorRequest($magazine, $user);

        $this->entityManager->persist($request);
        $this->entityManager->flush();
    }
}
