<?php declare(strict_types = 1);

namespace App\Controller\Entry\Comment;

use App\Entity\EntryComment;
use App\PageView\EntryCommentPageView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method getJsonFormResponse(FormInterface $form, string $string, ?array $variables = null)
 * @method render(string $template, array $array, Response $param)
 */
trait CommentResponseTrait
{
    private function getEntryCommentPageResponse(
        string $template,
        EntryCommentPageView $criteria,
        FormInterface $form,
        Request $request,
        ?EntryComment $parent = null,

    ): Response {
        if ($request->isXmlHttpRequest()) {
            $this->getJsonFormResponse($form, 'entry/comment/_form.html.twig');
        }

        $comments = $this->repository->findByCriteria($criteria);
        $this->repository->hydrate(...$comments);
        $this->repository->hydrateChildren(...$comments);

        return $this->render(
            $template,
            [
                'magazine' => $criteria->entry->magazine,
                'entry'    => $criteria->entry,
                'comments' => $comments,
                'parent'   => $parent,
                'comment'  => $parent,
                'form'     => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    private function getJsonCommentSuccessResponse(EntryComment $comment): Response
    {
        return new JsonResponse(
            [
                'id' => $comment->getId(),
                'html' => $this->renderView(
                    'entry/comment/_comment.html.twig',
                    [
                        'comment'       => $comment,
                    ]
                ),
            ]
        );
    }
}
