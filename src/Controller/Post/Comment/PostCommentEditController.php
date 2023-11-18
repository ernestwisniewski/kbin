<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Post\Comment;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Kbin\PostComment\DTO\PostCommentDto;
use App\Kbin\PostComment\Factory\PostCommentFactory;
use App\Kbin\PostComment\Form\PostCommentType;
use App\Kbin\PostComment\PostCommentEdit;
use App\PageView\PostCommentPageView;
use App\Repository\PostCommentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostCommentEditController extends AbstractController
{
    use PostCommentResponseTrait;

    public function __construct(
        private readonly PostCommentEdit $postCommentEdit,
        private readonly PostCommentFactory $postCommentFactory,
        private readonly PostCommentRepository $repository
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('edit', subject: 'comment')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        Request $request,
    ): Response {
        $dto = $this->postCommentFactory->createDto($comment);

        $form = $this->getCreateForm($dto, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            return $this->handleValidRequest($dto, $comment, $request);
        }

        $criteria = new PostCommentPageView($this->getPageNb($request));
        $criteria->post = $post;

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse(
                $form,
                'post/comment/_form_comment.html.twig',
                ['comment' => $comment, 'post' => $post, 'edit' => true]
            );
        }

        $comments = $this->repository->findByCriteria($criteria);

        return $this->render(
            'post/comment/edit.html.twig',
            [
                'magazine' => $post->magazine,
                'post' => $post,
                'comments' => $comments,
                'comment' => $comment,
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    private function getCreateForm(PostCommentDto $dto, PostComment $comment): FormInterface
    {
        return $this->createForm(
            PostCommentType::class,
            $dto,
            [
                'action' => $this->generateUrl(
                    'post_comment_edit',
                    [
                        'magazine_name' => $comment->magazine->name,
                        'post_id' => $comment->post->getId(),
                        'comment_id' => $comment->getId(),
                    ]
                ),
            ]
        );
    }

    private function handleValidRequest(PostCommentDto $dto, PostComment $comment, Request $request): Response
    {
        $comment = ($this->postCommentEdit)($comment, $dto);

        if ($request->isXmlHttpRequest()) {
            return $this->getPostCommentJsonSuccessResponse($comment);
        }

        return $this->redirectToPost($comment->post);
    }
}
