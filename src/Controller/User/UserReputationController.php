<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Repository\ReputationRepository;
use App\Service\ReputationManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserReputationController extends AbstractController
{
    public function __construct(private ReputationRepository $repository, private ReputationManager $manager)
    {
    }

    public function __invoke(User $user, ?string $reputationType, Request $request): Response
    {
        $page = (int) $request->get('p', 1);

        $results = match ($this->manager->resolveType($reputationType)) {
            ReputationRepository::TYPE_ENTRY => $this->repository->getUserReputation($user, Entry::class, $page),
            ReputationRepository::TYPE_ENTRY_COMMENT => $this->repository->getUserReputation($user, EntryComment::class, $page),
            ReputationRepository::TYPE_POST => $this->repository->getUserReputation($user, Post::class, $page),
            ReputationRepository::TYPE_POST_COMMENT => $this->repository->getUserReputation($user, PostComment::class, $page),
            default => null,
        };

        return $this->render(
            'user/reputation.html.twig',
            [
                'type' => $reputationType,
                'user' => $user,
                'results' => $results,
            ]
        );
    }
}
