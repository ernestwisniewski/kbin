<?php

declare(strict_types=1);

namespace App\Controller\Post\Comment;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostCommentVotersController extends AbstractController
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
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('_layout/_voters_inline.html.twig', [
                    'votes' => $comment->getUpVotes(),
                    'more' => null,
                ]),
            ]);
        }

        return $this->render('post/comment/voters.html.twig', [
            'magazine' => $magazine,
            'post' => $post,
            'comment' => $comment,
            'votes' => $comment->getUpVotes(),
        ]);
    }
}
