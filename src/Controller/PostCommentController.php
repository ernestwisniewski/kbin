<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PostCommentRepository;
use App\PageView\PostCommentPageView;
use App\Service\PostCommentManager;
use App\Form\PostCommentType;
use App\Entity\PostComment;
use App\DTO\PostCommentDto;
use App\Entity\Magazine;
use App\Entity\Post;

class PostCommentController extends AbstractController
{
    public function __construct(private PostCommentManager $commentManager)
    {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     * @ParamConverter("parent", options={"mapping": {"parent_comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("create_content", subject="magazine")
     */
    public function create(
        Magazine $magazine,
        Post $post,
        ?PostComment $parent,
        Request $request,
        PostCommentRepository $commentRepository
    ): Response {
        $commentDto = (new PostCommentDto())->createWithParent($post, $parent);

        $form = $this->createForm(
            PostCommentType::class,
            $commentDto,
            [
                'action' => $this->generateUrl(
                    'post_comment_create',
                    ['magazine_name' => $magazine->name, 'post_id' => $post->getId(), 'parent_comment_id' => $parent ? $parent->getId() : null]
                ),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $this->commentManager->create($commentDto, $this->getUserOrThrow());

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(
                    [
                        'html' => $this->renderView(
                            'post/comment/_comment.html.twig',
                            [
                                'extra_classes' => 'kbin-comment',
                                'with_parent'   => false,
                                'comment'       => $comment,
                                'level'         => 1,
                                'nested'        => false,
                            ]
                        ),
                    ]
                );
            }

            return $this->redirectToRoute(
                'post_single',
                [
                    'magazine_name' => $magazine->name,
                    'post_id'       => $post->getId(),
                ]
            );
        }

        $criteria       = (new PostCommentPageView($this->getPageNb($request)));
        $criteria->post = $post;

        $comments = $commentRepository->findByCriteria($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'form' => $this->renderView(
                        'post/comment/_form.html.twig',
                        [
                            'form' => $form->createView(),
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'post/comment/create.html.twig',
            [
                'magazine' => $magazine,
                'post'     => $post,
                'comments' => $comments,
                'parent'   => $parent,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="comment")
     */
    public function edit(
        Magazine $magazine,
        Post $post,
        PostComment $comment,
        Request $request,
        PostCommentRepository $commentRepository
    ): Response {
        $commentDto = $this->commentManager->createDto($comment);

        $form = $this->createForm(PostCommentType::class, $commentDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentManager->edit($comment, $commentDto);

            return $this->redirectToRoute(
                'post_single',
                [
                    'magazine_name' => $magazine->name,
                    'post_id'       => $post->getId(),
                ]
            );
        }

        $criteria       = (new PostCommentPageView($this->getPageNb($request)));
        $criteria->post = $post;

        $comments = $commentRepository->findByCriteria($criteria);

        return $this->render(
            'post/comment/edit.html.twig',
            [
                'magazine' => $magazine,
                'post'     => $post,
                'comments' => $comments,
                'comment'  => $comment,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="comment")
     */
    public function delete(Magazine $magazine, PostComment $comment, Request $request): Response
    {
        $this->validateCsrf('post_comment_delete', $request->request->get('token'));

        $this->commentManager->delete($comment, !$comment->isAuthor($this->getUserOrThrow()));

        return $this->redirectToRoute(
            'front_magazine',
            [
                'name' => $magazine->name,
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="comment")
     */
    public function purge(Magazine $magazine, PostComment $comment, Request $request): Response
    {
        $this->validateCsrf('post_comment_purge', $request->request->get('token'));

        $this->commentManager->purge($comment);

        return $this->redirectToRoute(
            'front_magazine',
            [
                'name' => $magazine->name,
            ]
        );
    }

    public function commentForm(string $magazineName, int $postId): Response
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
            ]
        );
    }
}
