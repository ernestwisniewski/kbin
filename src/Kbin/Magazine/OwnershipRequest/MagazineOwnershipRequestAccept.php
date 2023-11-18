<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine\OwnershipRequest;

use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Magazine\DTO\MagazineModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Kbin\Magazine\Moderator\MagazineModeratorRemove;
use App\Repository\MagazineOwnershipRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class MagazineOwnershipRequestAccept
{
    public function __construct(
        private MagazineModeratorRemove $magazineModeratorRemove,
        private MagazineModeratorAdd $magazineModeratorAdd,
        private MagazineOwnershipRequestRepository $magazineOwnershipRequestRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user): void
    {
        $this->entityManager->beginTransaction();

        try {
            ($this->magazineModeratorRemove)($magazine->getOwnerModerator());

            ($this->magazineModeratorAdd)(new MagazineModeratorDto($magazine, $user), true);

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
