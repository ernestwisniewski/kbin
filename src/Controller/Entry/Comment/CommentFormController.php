<?php declare(strict_types = 1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Form\EntryCommentType;
use Symfony\Component\HttpFoundation\Response;

class CommentFormController extends AbstractController
{
    public function __invoke(string $magazineName, int $entryId, int $commentId = null): Response
    {
        $routeParams = [
            'magazine_name' => $magazineName,
            'entry_id'      => $entryId,
        ];

        if ($commentId !== null) {
            $routeParams['comment_id'] = $commentId;
        }

        $form = $this->createForm(EntryCommentType::class, null, ['action' => $this->generateUrl('entry_comment_create', $routeParams)]);

        return $this->render(
            'entry/comment/_form.html.twig',
            [
                'form' => $form->createView(),
                new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200),
            ]
        );
    }
}
