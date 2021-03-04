<?php

namespace App\Controller;

use App\DTO\PostCommentDto;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Form\PostCommentType;
use App\Form\PostType;
use App\PageView\PostCommentPageView;
use App\Repository\PostCommentRepository;
use App\Service\PostCommentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostCommentController extends AbstractController
{
    private PostCommentManager $commentManager;

    public function __construct(PostCommentManager $commentManager)
    {
        $this->commentManager = $commentManager;
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
                    ['magazine_name' => $magazine->getName(), 'post_id' => $post->getId(), 'parent_comment_id' => $parent ? $parent->getId() : null]
                ),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentManager->create($commentDto, $this->getUserOrThrow());

            return $this->redirectToRoute(
                'post_single',
                [
                    'magazine_name' => $magazine->getName(),
                    'post_id'       => $post->getId(),
                ]
            );
        }

        $criteria = (new PostCommentPageView((int) $request->get('strona', 1)))
            ->showPost($post);

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
                    'magazine_name' => $magazine->getName(),
                    'post_id'       => $post->getId(),
                ]
            );
        }

        $criteria = (new PostCommentPageView((int) $request->get('strona', 1)))
            ->showPost($post);

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

        $this->commentManager->delete($comment);

        return $this->redirectToRoute(
            'front_magazine',
            [
                'name' => $magazine->getName(),
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
                'name' => $magazine->getName(),
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
