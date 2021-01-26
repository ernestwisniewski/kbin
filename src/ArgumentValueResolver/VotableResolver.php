<?php declare(strict_types = 1);

namespace App\ArgumentValueResolver;

use App\Entity\Entry;
use App\Entity\Votable;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EntryRepository;

class VotableResolver implements ArgumentValueResolverInterface
{
    private EntryRepository $entryRepository;

    public function __construct(EntryRepository $entryRepository)
    {
        $this->entryRepository = $entryRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === Votable::class
            && !$argument->isVariadic()
            && $request->attributes->has('entityClass')
            && \in_array(
                $request->attributes->get('entityClass'),
                [
                    Entry::class,
                ],
                true
            )
            && $request->attributes->has('id');
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        ['id' => $id, 'entityClass' => $entityClass] = $request->attributes->all();

        switch ($entityClass) {
            case Entry::class:
                return yield $this->entryRepository->find($id);
        }

        throw new \LogicException();
    }
}
