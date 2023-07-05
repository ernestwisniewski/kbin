<?php

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Factory\ActivityPub\PostNoteFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends AbstractController
{
    public function __construct(private readonly PostNoteFactory $postNoteFactory)
    {
    }

    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['post_id' => 'id'])]
        Post $post,
        Request $request
    ): Response {
        if ($post->apId) {
            return $this->redirect($post->apId);
        }

        $response = new JsonResponse($this->postNoteFactory->create($post, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
