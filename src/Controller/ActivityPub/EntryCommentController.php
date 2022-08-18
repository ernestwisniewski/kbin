<?php declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Factory\ActivityPub\EntryCommentNoteFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCommentController extends AbstractController
{
    public function __construct(private EntryCommentNoteFactory $pageFactory)
    {
    }

    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('entry', options: ['mapping' => ['entry_id' => 'id']])]
    #[ParamConverter('comment', options: ['mapping' => ['comment_id' => 'id']])]
    public function __invoke(
        Magazine $magazine,
        Entry $entry,
        EntryComment $comment,
        Request $request
    ): Response {
        if ($comment->apId) {
            $this->redirect($comment->apId);
        }

        $response = new JsonResponse($this->pageFactory->create($comment, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
