<?php declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Factory\MagazineFactory;
use Webmozart\Assert\Assert;
use App\DTO\MagazineDto;
use App\Entity\Magazine;
use App\Entity\User;

class MagazineManager
{
    private MagazineFactory $magazineFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(MagazineFactory $magazineFactory, EntityManagerInterface $entityManager)
    {
        $this->magazineFactory = $magazineFactory;
        $this->entityManager   = $entityManager;
    }

    public function create(MagazineDto $magazineDto, User $user): Magazine
    {
        $magazine = $this->magazineFactory->createFromDto($magazineDto, $user);

        $this->entityManager->persist($magazine);
        $this->entityManager->flush();

        return $magazine;
    }

    public function edit(Magazine $magazine, MagazineDto $magazineDto): Magazine
    {
        Assert::same($magazine->getName(), $magazineDto->getName());

        $magazine->setTitle($magazineDto->getTitle());
        $magazine->setDescription($magazineDto->getDescription());
        $magazine->setRules($magazineDto->getRules());

        $this->entityManager->flush();

        return $magazine;
    }

    public function purge(Magazine $magazine): void
    {
        $this->entityManager->remove($magazine);
        $this->entityManager->flush();
    }

    public function createDto(Magazine $magazine): MagazineDto
    {
        return ($this->magazineFactory->createDto($magazine))->setId($magazine->getId());
    }
}
