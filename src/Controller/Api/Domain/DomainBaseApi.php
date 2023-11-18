<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Domain;

use App\Controller\Api\BaseApi;
use App\DTO\DomainDto;
use App\Entity\Domain;
use App\Factory\DomainFactory;
use Symfony\Contracts\Service\Attribute\Required;

class DomainBaseApi extends BaseApi
{
    private readonly DomainFactory $factory;

    #[Required]
    public function setFactory(DomainFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Serialize a domain to JSON.
     */
    protected function serializeDomain(DomainDto|Domain $dto)
    {
        $response = $dto instanceof Domain ? $this->factory->createDto($dto) : $dto;

        return $response;
    }
}
