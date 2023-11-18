<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ClientConsentsResponseDto;
use App\Entity\OAuth2UserConsent;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;

class ClientConsentsFactory
{
    public function __construct(
        private readonly ImageFactory $imageFactory,
    ) {
    }

    public function createDto(OAuth2UserConsent $consent): ClientConsentsResponseDto
    {
        return ClientConsentsResponseDto::create(
            $consent->getId(),
            $consent->getClient()->getName(),
            $consent->getClient()->getDescription(),
            $consent->getClient()->getImage() ? $this->imageFactory->createDto($consent->getClient()->getImage()) : null,
            $consent->getScopes(),
            array_map(fn (Scope $scope) => (string) $scope, $consent->getClient()->getScopes()),
        );
    }
}
