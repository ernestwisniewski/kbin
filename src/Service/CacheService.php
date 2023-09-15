<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Contracts\VotableInterface;
use Doctrine\ORM\EntityManagerInterface;

class CacheService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function getVotersCacheKey(VotableInterface $subject): string
    {
        return "voters_{$this->getKey($subject)}_{$subject->getId()}";
    }

    private function getKey(VotableInterface|FavouriteInterface $subject): string
    {
        $className = $this->entityManager->getClassMetadata(\get_class($subject))->name;
        $className = explode('\\', $className);

        return end($className);
    }

    public function getFavouritesCacheKey(FavouriteInterface $subject): string
    {
        return "favourites_{$this->getKey($subject)}_{$subject->getId()}";
    }
}
