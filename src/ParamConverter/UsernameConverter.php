<?php declare(strict_types=1);

namespace App\ParamConverter;

use App\Entity\User;
use App\Repository\UserRepository;
use JetBrains\PhpStorm\Pure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UsernameConverter implements ParamConverterInterface
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $username = $request->attributes->get('username') ?? $request->attributes->get('user');

        // @todo case-insensitive
        if (!$user = $this->repository->findOneByUsername($username)) {
            throw new NotFoundHttpException();
        }

        $request->attributes->set($configuration->getName(), $user);
    }

    #[Pure] public function supports(ParamConverter $configuration): bool
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        if ($configuration->getClass() !== User::class) {
            return false;
        }

        // @todo test coverage
        return false;
    }
}
