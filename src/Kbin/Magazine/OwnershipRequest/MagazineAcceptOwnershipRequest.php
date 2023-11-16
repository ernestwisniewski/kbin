<?php

declare(strict_types=1);

namespace App\Kbin\Magazine\OwnershipRequest;

use App\DTO\ModeratorDto;
use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Magazine\Moderator\MagazineAddModerator;
use App\Kbin\Magazine\Moderator\MagazineRemoveModerator;
use App\Repository\MagazineOwnershipRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class MagazineAcceptOwnershipRequest
{
    public function __construct(
        private MagazineRemoveModerator $magazineRemoveModerator,
        private MagazineAddModerator $magazineAddModerator,
        private MagazineOwnershipRequestRepository $magazineOwnershipRequestRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user): void
    {
        $this->entityManager->beginTransaction();

        try {
            ($this->magazineRemoveModerator)($magazine->getOwnerModerator());

            ($this->magazineAddModerator)(new ModeratorDto($magazine, $user), true);

            $request = $this->magazineOwnershipRequestRepository->findOneBy([
                'magazine' => $magazine,
                'user' => $user,
            ]);

            $this->entityManager->remove($request);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            return;
        }
    }
}
