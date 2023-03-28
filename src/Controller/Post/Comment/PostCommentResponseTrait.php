<?php

declare(strict_types=1);

namespace App\Controller\Post\Comment;

use App\Entity\PostComment;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait PostCommentResponseTrait
{
    private function getPostCommentJsonSuccessResponse(PostComment $comment): Response
    {
        return new JsonResponse(
            [
                'id' => $comment->getId(),
                'html' => $this->renderView(
                    'components/_ajax.html.twig',
                    [
                        'component' => 'post_comment',
                        'attributes' => [
                            'comment' => $comment,
                            'showEntryTitle' => false,
                        ],
                    ]
                ),
            ]
        );
    }
}
