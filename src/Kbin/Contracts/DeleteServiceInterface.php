<?php

declare(strict_types=1);

namespace App\Kbin\Contracts;

use App\Entity\Contracts\ContentInterface;
use App\Entity\User;

interface DeleteServiceInterface
{
    public function __invoke(User $user, ContentInterface $subject);
}
