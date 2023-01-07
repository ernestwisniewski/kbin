<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

interface TagInterface
{
    public function getTags(): array;
}
