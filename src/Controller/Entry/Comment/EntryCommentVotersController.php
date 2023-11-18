<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Contracts\VotableInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCommentVotersController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        Request $request,
        string $type
    ): Response {
        $votes = $comment->votes->filter(
            fn ($e) => $e->choice === ('up' === $type ? VotableInterface::VOTE_UP : VotableInterface::VOTE_DOWN)
        );

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('_layout/_voters_inline.html.twig', [
                    'votes' => $votes,
                    'more' => null,
                ]),
            ]);
        }

        return $this->render('entry/comment/voters.html.twig', [
            'magazine' => $magazine,
            'entry' => $entry,
            'comment' => $comment,
            'votes' => $votes,
        ]);
    }
}
