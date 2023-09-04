<?php

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
        public ?array $instances
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'instances' => $this->instances,
        ];
    }
}
