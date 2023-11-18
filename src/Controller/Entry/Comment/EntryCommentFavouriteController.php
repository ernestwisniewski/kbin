<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCommentFavouriteController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        Request $request
    ): Response {
        return $this->render('entry/comment/favourites.html.twig', [
            'magazine' => $magazine,
            'entry' => $entry,
            'comment' => $comment,
            'favourites' => $comment->favourites,
        ]);
    }
}
