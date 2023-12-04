<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Entity\User;
use App\Exception\UserCannotBeBanned;
use App\Kbin\Magazine\DTO\MagazineBanDto;
use App\Kbin\Magazine\EventSubscriber\Event\MagazineBanEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

readonly class MagazineBan
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(
        Magazine $magazine,
        User $user,
        User $bannedBy,
        MagazineBanDto $dto
    ): ?\App\Entity\MagazineBan {
        if ($user->isAdmin() || $magazine->userIsModerator($user)) {
            throw new UserCannotBeBanned();
        }

        Assert::nullOrGreaterThan($dto->expiredAt, new \DateTime());

        $ban = $magazine->addBan($user, $bannedBy, $dto->reason, $dto->expiredAt);

        if (!$ban) {
            return null;
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineBanEvent($ban));

        return $ban;
    }
}
