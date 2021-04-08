<?php declare(strict_types=1);

namespace App\Form\DataTransformer;

use InvalidArgumentException;
use Symfony\Component\Form\DataTransformerInterface;
use App\Repository\UserRepository;
use App\Entity\User;

class UserTransformer implements DataTransformerInterface
{
    public function __construct(private UserRepository $userRepository)
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

        return $this->userRepository->findOneByUsername($value);
    }
}
