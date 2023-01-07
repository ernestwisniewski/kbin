<?php

namespace App\Repository\Contract;

interface TagRepositoryInterface
{
    public function findWithTags(): array;
}
