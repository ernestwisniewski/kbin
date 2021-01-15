<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\DTO\MagazineDto;
use App\Entity\Magazine;
use App\Entity\User;

class MagazineManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function create(MagazineDto $magazineDto, User $user): Magazine {
        $magazine = new Magazine(
            $magazineDto->getName(),
            $magazineDto->getTitle(),
            $user
        );

        $this->entityManager->persist($magazine);

        return $magazine;
    }
}
