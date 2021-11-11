<?php declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCommentVotersController extends AbstractController
{
    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     */
    public function __invoke(
        Magazine $magazine,
        Entry $entry,
        EntryComment $comment,
        Request $request
    ): Response {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('_layout/_voters_inline.html.twig', [
                    'votes' => $comment->votes,
                    'more'  => null,
                ]),
            ]);
        }

        return $this->render('entry/comment/voters.html.twig', [
            'magazine' => $magazine,
            'entry'    => $entry,
            'comment'  => $comment,
            'votes'    => $entry->votes,
        ]);
    }
}
