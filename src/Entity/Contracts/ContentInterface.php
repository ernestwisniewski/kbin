<?php declare(strict_types=1);

namespace App\Entity\Contracts;

use App\Entity\Magazine;
use App\Entity\User;

interface ContentInterface
{
    public function getMagazine(): ?Magazine;

    public function getUser(): ?User;
}
