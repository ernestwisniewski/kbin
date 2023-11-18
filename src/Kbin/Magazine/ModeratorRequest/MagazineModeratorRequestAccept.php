<?php

declare(strict_types=1);

namespace App\Kbin\Magazine\ModeratorRequest;

use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Magazine\DTO\MagazineModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Repository\ModeratorRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class MagazineModeratorRequestAccept
{
    public function __construct(
        private MagazineModeratorAdd $magazineModeratorAdd,
        private ModeratorRequestRepository $moderatorRequestRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user): void
    {
        ($this->magazineModeratorAdd)(new MagazineModeratorDto($magazine, $user));

        $request = $this->moderatorRequestRepository->findOneBy([
            'magazine' => $magazine,
            'user' => $user,
        ]);

        $this->entityManager->remove($request);
        $this->entityManager->flush();
    }
}
