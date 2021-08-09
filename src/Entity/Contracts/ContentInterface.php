<?php declare(strict_types=1);

namespace App\Entity\Contracts;

use App\Entity\Magazine;

interface ContentInterface
{
    public function getMagazine(): ?Magazine;
}
