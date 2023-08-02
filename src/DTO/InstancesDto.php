<?php

declare(strict_types=1);

namespace App\DTO;

class InstancesDto
{
    public function __construct(public ?array $instances)
    {
    }
}
