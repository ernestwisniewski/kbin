<?php

declare(strict_types=1);

namespace App\Controller\Post\Comment;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\PostComment;
use App\Kbin\PostComment\PostCommentDelete;
use App\Kbin\PostComment\PostCommentPurge;
use App\Kbin\PostComment\PostCommentRestore;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostCommentDeleteController extends AbstractController
{
    public function __construct(
        private readonly PostCommentDelete $postCommentDelete,
        private readonly PostCommentRestore $postCommentRestore,
        private readonly PostCommentPurge $postCommentPurge,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'comment')]
    public function delete(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        Request $request
    ): Response {
        $this->validateCsrf('post_comment_delete', $request->request->get('token'));

        ($this->postCommentDelete)($this->getUserOrThrow(), $comment);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'comment')]
    public function restore(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        Request $request
    ): Response {
        $this->validateCsrf('post_comment_restore', $request->request->get('token'));

        ($this->postCommentRestore)($this->getUserOrThrow(), $comment);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('purge', subject: 'comment')]
    public function purge(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        Request $request
    ): Response {
        $this->validateCsrf('post_comment_purge', $request->request->get('token'));

        ($this->postCommentPurge)($this->getUserOrThrow(), $comment);

        return $this->redirectToRefererOrHome($request);
    }
}
