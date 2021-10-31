<?php declare(strict_types = 1);

namespace App\Form\DataTransformer;

use App\Entity\User;
use App\Repository\UserRepository;
use InvalidArgumentException;
use Symfony\Component\Form\DataTransformerInterface;

class UserTransformer implements DataTransformerInterface
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function transform($value): ?string
    {
        if ($value instanceof User) {
            return $value->getUsername();
        }

        if ($value !== null) {
            throw new InvalidArgumentException('$value must be '.User::class.' or null');
        }

        return null;
    }

    public function reverseTransform($value): ?User
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->repository->findOneByUsername($value);
    }
}
