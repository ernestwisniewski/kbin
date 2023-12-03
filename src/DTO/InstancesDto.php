<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema()]
class InstancesDto implements \JsonSerializable
{
    public function __construct(
        #[Assert\All([
            new Assert\Hostname(),
        ])]
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string', format: 'url'))]
        public ?array $instances = []
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'instances' => $this->instances,
        ];
    }
}
