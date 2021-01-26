<?php declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Votable;
use App\Service\VoteManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VoteController extends AbstractController
{
    public function __invoke(Votable $votable, int $choice, VoteManager $voteManager, Request $request): Response
    {
        $voteManager->vote($choice, $votable, $this->getUserOrThrow());
    }
}
