<?php declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Factory\ActivityPub\EntryNoteFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCommentController
{
    public function __construct(private EntryNoteFactory $pageFactory)
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
        $response = new JsonResponse($this->pageFactory->create($comment));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
