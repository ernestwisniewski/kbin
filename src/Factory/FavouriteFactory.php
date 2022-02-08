<?php declare(strict_types=1);

namespace App\Factory;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Favourite;
use App\Entity\Report;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class FavouriteFactory
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createFromEntity(User $user, FavouriteInterface $subject): Favourite
    {
        $className = $this->entityManager->getClassMetadata(get_class($subject))->name.'Favourite';

        return new $className($user, $subject);
    }
}
