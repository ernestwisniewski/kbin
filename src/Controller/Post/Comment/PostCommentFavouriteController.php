<?php

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
        #[MapEntity(mapping: ['post_id' => 'id'])]
        Post $post,
        #[MapEntity(mapping: ['comment_id' => 'id'])]
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
