<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Domain;
use App\Entity\User;
use App\Kbin\Domain\DTO\DomainDto;
use Symfony\Bundle\SecurityBundle\Security;

readonly class DomainFactory
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function createDto(Domain $domain): DomainDto
    {
        $dto = DomainDto::create(
            $domain->name,
            $domain->entryCount,
            $domain->subscriptionsCount,
            $domain->getId(),
        );

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        // Only return the user's vote if permission to control voting has been given
        $dto->isUserSubscribed = $this->security->isGranted('ROLE_OAUTH2_DOMAIN:SUBSCRIBE')
            ? $domain->isSubscribed($currentUser)
            : null;
        $dto->isBlockedByUser = $this->security->isGranted('ROLE_OAUTH2_DOMAIN:BLOCK')
            ? $currentUser->isBlockedDomain($domain)
            : null;

        return $dto;
    }
}
