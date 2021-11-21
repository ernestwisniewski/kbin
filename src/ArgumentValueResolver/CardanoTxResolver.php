<?php declare(strict_types=1);

namespace App\ArgumentValueResolver;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Repository\EntryRepository;
use Generator;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use function in_array;

class CardanoTxResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private EntryRepository $entryRepository,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === ContentInterface::class
            && !$argument->isVariadic()
            && $request->attributes->has('entityClass')
            && in_array(
                $request->attributes->get('entityClass'),
                [
                    Entry::class,
                ],
                true
            )
            && $request->attributes->has('id');
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        ['id' => $id, 'entityClass' => $entityClass] = $request->attributes->all();

        return match ($entityClass) {
            Entry::class => yield $this->entryRepository->find($id),
            default => throw new LogicException(),
        };
    }
}
