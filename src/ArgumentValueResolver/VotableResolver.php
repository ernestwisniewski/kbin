<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\ArgumentValueResolver;

use App\Entity\Contracts\VotableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class VotableResolver implements ValueResolverInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (
            VotableInterface::class === $argument->getType()
            && !$argument->isVariadic()
            && is_a($request->attributes->get('entityClass'), VotableInterface::class, true)
            && $request->attributes->has('id')
        ) {
            ['id' => $id, 'entityClass' => $entityClass] = $request->attributes->all();

            yield $this->entityManager->find($entityClass, $id);
        }
    }
}
