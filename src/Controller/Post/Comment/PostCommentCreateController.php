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
use App\Kbin\PostComment\Form\PostCommentType;
use App\Kbin\PostComment\PostCommentCreate;
use App\Kbin\PostComment\PostCommentPageView;
use App\Repository\PostCommentRepository;
use App\Service\IpResolver;
use App\Service\MentionManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostCommentCreateController extends AbstractController
{
    use PostCommentResponseTrait;

    public function __construct(
        private readonly PostCommentCreate $postCommentCreate,
        private readonly PostCommentRepository $repository,
        private readonly IpResolver $ipResolver,
        private readonly MentionManager $mentionManager
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('comment', subject: 'post')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        #[MapEntity(id: 'parent_comment_id')]
        ?PostComment $parent,
        Request $request,
    ): Response {
        $form = $this->getForm($post, $parent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();
            $dto->post = $post;
            $dto->magazine = $magazine;
            $dto->parent = $parent;
            $dto->ip = $this->ipResolver->resolve();

            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            return $this->handleValidRequest($dto, $request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse(
                $form,
                'post/comment/_form_comment.html.twig',
                ['post' => $post, 'parent' => $parent]
            );
        }

        $criteria = new PostCommentPageView($this->getPageNb($request));
        $criteria->post = $post;

        $comments = $this->repository->findByCriteria($criteria);

        return $this->render(
            'post/comment/create.html.twig',
            [
                'magazine' => $magazine,
                'post' => $post,
                'comments' => $comments,
                'parent' => $parent,
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    private function getForm(Post $post, ?PostComment $parent): FormInterface
    {
        $dto = new PostCommentDto();

        if ($parent && $this->getUser()->addMentionsPosts) {
            $handle = $this->mentionManager->addHandle([$parent->user->username])[0];

            if ($parent->user !== $this->getUser()) {
                $dto->body = $handle;
            } else {
                $dto->body .= PHP_EOL;
            }

            if ($parent->mentions) {
                $mentions = $this->mentionManager->addHandle($parent->mentions);
                $mentions = array_filter(
                    $mentions,
                    fn (string $mention) => $mention !== $handle && $mention !== $this->mentionManager->addHandle(
                        [$this->getUser()->username]
                    )[0]
                );

                $dto->body .= PHP_EOL.PHP_EOL;
                $dto->body .= implode(' ', array_unique($mentions));
            }
        } elseif ($this->getUser()->addMentionsPosts) {
            if ($post->user !== $this->getUser()) {
                $dto->body = $this->mentionManager->addHandle([$post->user->username])[0];
            }
        }

        return $this->createForm(
            PostCommentType::class,
            $dto,
            [
                'action' => $this->generateUrl(
                    'post_comment_create',
                    [
                        'magazine_name' => $post->magazine->name,
                        'post_id' => $post->getId(),
                        'parent_comment_id' => $parent?->getId(),
                    ]
                ),
                'parentLanguage' => $parent?->lang ?? $post->lang,
            ]
        );
    }

    private function handleValidRequest(PostCommentDto $dto, Request $request): Response
    {
        $comment = ($this->postCommentCreate)($dto, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return $this->getPostCommentJsonSuccessResponse($comment);
        }

        return $this->redirectToPost($comment->post);
    }
}
