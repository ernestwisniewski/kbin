<?php

declare(strict_types=1);

namespace App\Kbin\Magazine\ModeratorRequest;

use App\DTO\ModeratorDto;
use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Magazine\Moderator\MagazineAddModerator;
use App\Repository\ModeratorRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class MagazineAcceptModeratorRequest
{
    public function __construct(
        private MagazineAddModerator $magazineAddModerator,
        private ModeratorRequestRepository $moderatorRequestRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user): void
    {
        ($this->magazineAddModerator)(new ModeratorDto($magazine, $user));

        $request = $this->moderatorRequestRepository->findOneBy([
            'magazine' => $magazine,
            'user' => $user,
        ]);

        $this->entityManager->remove($request);
        $this->entityManager->flush();
    }
}
