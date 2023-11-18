<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Post\Comment;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostCommentFavouriteController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        Request $request,
    ): Response {
        return $this->render('post/comment/favourites.html.twig', [
            'magazine' => $magazine,
            'post' => $post,
            'comment' => $comment,
            'favourites' => $comment->favourites,
        ]);
    }
}
