<?php declare(strict_types = 1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(MagazineFactory $magazineFactory, EntityManagerInterface $entityManager)
    {
        $this->magazineFactory = $magazineFactory;
        $this->entityManager = $entityManager;
    }

    public function createMagazine(MagazineDto $magazineDto, User $user): Magazine
    {
        $magazine = $this->magazineFactory->createFromDto($magazineDto, $user);

        $this->entityManager->persist($magazine);

        return $magazine;
    }
}
