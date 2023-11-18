<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\ArgumentValueResolver;

use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class MagazineResolver implements ValueResolverInterface
{
    public function __construct(private readonly MagazineRepository $repository)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (Magazine::class !== $argument->getType()) {
            return;
        }

        $magazineName = $request->attributes->get('magazine_name') ?? $request->attributes->get('name');

        yield $this->repository->findOneByName($magazineName);
    }
}
