<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\DomainDto;
use App\Entity\Domain;
use App\Entity\User;
use App\Service\DomainManager;
use Symfony\Bundle\SecurityBundle\Security;

class DomainFactory
{
    public function __construct(
        private readonly Security $security,
        private readonly DomainManager $domainManager
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
        $dto->isUserSubscribed = $this->security->isGranted('ROLE_OAUTH2_DOMAIN:SUBSCRIBE') ? $domain->isSubscribed($currentUser) : null;
        $dto->isBlockedByUser = $this->security->isGranted('ROLE_OAUTH2_DOMAIN:BLOCK') ? $currentUser->isBlockedDomain($domain) : null;

        return $dto;
    }
}
