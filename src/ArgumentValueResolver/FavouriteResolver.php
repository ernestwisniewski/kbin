<?php

declare(strict_types=1);

namespace App\ArgumentValueResolver;

use App\Entity\Contracts\FavouriteInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class FavouriteResolver implements ValueResolverInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (
            FavouriteInterface::class === $argument->getType()
            && !$argument->isVariadic()
            && is_a($request->attributes->get('entityClass'), FavouriteInterface::class, true)
            && $request->attributes->has('id')
        ) {
            ['id' => $id, 'entityClass' => $entityClass] = $request->attributes->all();

            yield $this->entityManager->find($entityClass, $id);
        }
    }
}
