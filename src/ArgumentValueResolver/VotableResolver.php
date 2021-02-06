<?php declare(strict_types = 1);

namespace App\ArgumentValueResolver;

use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EntryCommentRepository;
use App\Entity\Contracts\VoteInterface;
use App\Repository\EntryRepository;
use App\Entity\EntryComment;
use App\Entity\Entry;

class VotableResolver implements ArgumentValueResolverInterface
{
    private EntryRepository $entryRepository;
    private EntryCommentRepository $entryCommentRepository;

    public function __construct(EntryRepository $entryRepository, EntryCommentRepository $entryCommentRepository)
    {
        $this->entryRepository = $entryRepository;
        $this->entryCommentRepository = $entryCommentRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === VoteInterface::class
            && !$argument->isVariadic()
            && $request->attributes->has('entityClass')
            && \in_array(
                $request->attributes->get('entityClass'),
                [
                    Entry::class,
                    EntryComment::class,
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
            case EntryComment::class:
                return yield $this->entryCommentRepository->find($id);
        }

        throw new \LogicException();
    }
}
