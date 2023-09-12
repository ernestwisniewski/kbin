<?php

declare(strict_types=1);

namespace App\Repository\Contract;

interface TagRepositoryInterface
{
    public function findWithTags(): array;
}
