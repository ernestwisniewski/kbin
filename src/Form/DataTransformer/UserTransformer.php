<?php declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserTransformer implements DataTransformerInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function transform($value): ?string
    {
        if ($value instanceof User) {
            return $value->getUsername();
        }

        if ($value !== null) {
            throw new \InvalidArgumentException('$value must be '.User::class.' or null');
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
