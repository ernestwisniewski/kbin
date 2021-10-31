<?php declare(strict_types = 1);

namespace App\Controller\Post\Comment;

use App\Controller\AbstractController;
use App\Form\PostCommentType;
use Symfony\Component\HttpFoundation\Response;

class CommentFormController extends AbstractController
{
    public function __invoke(string $magazineName, int $postId): Response
    {
        $form = $this->createForm(
            PostCommentType::class,
            null,
            ['action' => $this->generateUrl('post_comment_create', ['magazine_name' => $magazineName, 'post_id' => $postId])]
        );

        return $this->render(
            'post/comment/_form.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
