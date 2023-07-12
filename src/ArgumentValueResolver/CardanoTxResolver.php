<?php

declare(strict_types=1);

namespace App\ArgumentValueResolver;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Repository\EntryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class CardanoTxResolver implements ValueResolverInterface
{
    public function __construct(private readonly EntryRepository $entryRepository)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (
            ContentInterface::class === $argument->getType()
            && !$argument->isVariadic()
            && is_a($request->attributes->get('entityClass'), Entry::class, true)
            && $request->attributes->has('id')
        ) {
            yield $this->entryRepository->find($request->attributes->get('id'));
        }
    }
}
