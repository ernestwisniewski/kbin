<?php

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Controller\AbstractController;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Factory\ActivityPub\PostCommentNoteFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostCommentController extends AbstractController
{
    use PrivateContentTrait;

    public function __construct(private readonly PostCommentNoteFactory $commentNoteFactory)
    {
    }

    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['post_id' => 'id'])]
        Post $post,
        #[MapEntity(mapping: ['comment_id' => 'id'])]
        PostComment $comment,
        Request $request
    ): Response {
        if ($comment->apId) {
            return $this->redirect($comment->apId);
        }

        $this->handlePrivateContent($post);

        $response = new JsonResponse($this->commentNoteFactory->create($comment, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
