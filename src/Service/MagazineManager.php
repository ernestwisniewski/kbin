<?php declare(strict_types = 1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Factory\MagazineFactory;
use Webmozart\Assert\Assert;
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
        $this->entityManager   = $entityManager;
    }

    public function createMagazine(MagazineDto $magazineDto, User $user): Magazine
    {
        $magazine = $this->magazineFactory->createFromDto($magazineDto, $user);

        $this->entityManager->persist($magazine);
        $this->entityManager->flush();

        return $magazine;
    }

    public function editMagazine(Magazine $magazine, MagazineDto $magazineDto): Magazine
    {
        Assert::same($magazine->getName(), $magazineDto->getName());

        $magazine->setTitle($magazineDto->getTitle());

        $this->entityManager->flush();

        return $magazine;
    }

    public function createMagazineDto(Magazine $magazine): MagazineDto
    {
        return $this->magazineFactory->createDto($magazine);
    }

    public function purgeMagazine(Magazine $magazine): void
    {
        $this->entityManager->remove($magazine);
        $this->entityManager->flush();
    }
}
