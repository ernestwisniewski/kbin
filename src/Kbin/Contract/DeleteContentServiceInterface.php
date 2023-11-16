<?php

declare(strict_types=1);

namespace App\Kbin\Contract;

use App\Entity\Contracts\ContentInterface;
use App\Entity\User;

interface DeleteContentServiceInterface
{
    public function __invoke(User $user, ContentInterface $subject);
}
