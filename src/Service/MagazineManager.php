<?php

namespace App\Service;

use App\Factory\MagazineFactory;
use App\DTO\MagazineDto;
use App\Entity\Magazine;
use App\Entity\User;

class MagazineManager
{
    /**
     * @var MagazineFactory
     */
    private $magazineFactory;

    public function __construct(MagazineFactory $magazineFactory)
    {
        $this->magazineFactory = $magazineFactory;
    }

    public function createMagazine(MagazineDto $magazineDto, User $user): Magazine
    {
        return $this->magazineFactory->createFromDto($magazineDto, $user);
    }
}
