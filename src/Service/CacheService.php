<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\VoteInterface;
use Doctrine\ORM\EntityManagerInterface;

class CacheService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getVotersCacheKey(VoteInterface $subject): string
    {

        return "voters_{$this->getKey($subject)}_{$subject->getId()}";
    }

    public function getFavouritesCacheKey(VoteInterface $subject): string
    {
        return "favourites_{$this->getKey($subject)}_{$subject->getId()}";
    }

    private function getKey(VoteInterface $subject): string
    {
        $className = $this->entityManager->getClassMetadata(get_class($subject))->name;
        $className = explode('\\', $className);

        return end($className);
    }
}
