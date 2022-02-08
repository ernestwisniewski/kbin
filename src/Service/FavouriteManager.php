<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Favourite;
use App\Entity\User;
use App\Exception\FavouritedAlreadyException;
use App\Factory\FavouriteFactory;
use App\Repository\FavouriteRepository;
use Doctrine\ORM\EntityManagerInterface;

class FavouriteManager
{
    public function __construct(
        private FavouriteFactory $factory,
        private FavouriteRepository $repository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function toggle(User $user, FavouriteInterface $subject): ?Favourite
    {
        if (!($favourite = $this->repository->findBySubject($user, $subject))) {
            $favourite = $this->factory->createFromEntity($user, $subject);
            $this->entityManager->persist($favourite);
        } else {
            $this->entityManager->remove($favourite);
        }

        $this->entityManager->flush();

        return $favourite ?? null;
    }
}
