<?php

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Controller\AbstractController;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Factory\ActivityPub\EntryCommentNoteFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCommentController extends AbstractController
{
    use PrivateContentTrait;

    public function __construct(private readonly EntryCommentNoteFactory $commentNoteFactory)
    {
    }

    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['entry_id' => 'id'])]
        Entry $entry,
        #[MapEntity(mapping: ['comment_id' => 'id'])]
        EntryComment $comment,
        Request $request
    ): Response {
        if ($comment->apId) {
            return $this->redirect($comment->apId);
        }

        $this->handlePrivateContent($comment);

        $response = new JsonResponse($this->commentNoteFactory->create($comment, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
