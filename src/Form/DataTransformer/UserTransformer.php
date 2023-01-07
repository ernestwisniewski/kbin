<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;

class UserTransformer implements DataTransformerInterface
{
    public function __construct(private readonly UserRepository $repository)
    {
    }

    public function transform($value): ?string
    {
        if ($value instanceof User) {
            return $value->getUsername();
        }

        if (null !== $value) {
            throw new \InvalidArgumentException('$value must be '.User::class.' or null');
        }

        return null;
    }

    public function reverseTransform($value): ?User
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return $this->repository->findOneByUsername($value);
    }
}
