<?php

declare(strict_types=1);

namespace App\Kbin\Magazine\OwnershipRequest;

use App\Entity\Magazine;
use App\Entity\MagazineOwnershipRequest;
use App\Entity\User;
use App\Repository\MagazineOwnershipRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class MagazineOwnershipRequestToggle
{
    public function __construct(
        private MagazineOwnershipRequestRepository $magazineOwnershipRequestRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(
        Magazine $magazine,
        User $user
    ): void {
        $request = $this->magazineOwnershipRequestRepository->findOneBy([
            'magazine' => $magazine,
            'user' => $user,
        ]);

        if ($request) {
            $this->entityManager->remove($request);
            $this->entityManager->flush();

            return;
        }

        $request = new MagazineOwnershipRequest($magazine, $user);

        $this->entityManager->persist($request);
        $this->entityManager->flush();
    }
}
