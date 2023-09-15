<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts\VotableInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Service\VoteManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class VoteController extends AbstractController
{
    public function __construct(private readonly VoteManager $manager)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('vote', subject: 'votable')]
    public function __invoke(VotableInterface $votable, int $choice, Request $request): Response
    {
        $this->validateCsrf('vote', $request->request->get('token'));

        $vote = $this->manager->vote($choice, $votable, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView('components/_ajax.html.twig', [
                            'component' => 'vote',
                            'attributes' => [
                                'subject' => $vote->getSubject(),
                                'showDownvote' => str_contains(\get_class($vote->getSubject()), 'Entry'),
                            ],
                        ]
                    ),
                ]
            );
        }

        if (!$request->headers->has('Referer')) {
            return $this->redirectToRoute('front', ['_fragment' => $this->getFragment($votable)]);
        }

        return $this->redirect($request->headers->get('Referer').'#'.$this->getFragment($votable));
    }

    public function getFragment($votable): string
    {
        return match (true) {
            $votable instanceof Entry => 'entry-'.$votable->getId(),
            $votable instanceof EntryComment => 'entry-comment-'.$votable->getId(),
            $votable instanceof Post => 'post-'.$votable->getId(),
            $votable instanceof PostComment => 'post-comment-'.$votable->getId(),
            default => throw new \InvalidArgumentException('Invalid votable type'),
        };
    }
}
