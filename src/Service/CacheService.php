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
        $className = $this->entityManager->getClassMetadata(get_class($subject))->name;
        $className = explode('\\', $className);
        $className = end($className);

        return "voters_{$className}_{$subject->getId()}";
    }
}
