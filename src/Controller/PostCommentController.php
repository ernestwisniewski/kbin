<?php

namespace App\Controller;

use App\DTO\PostCommentDto;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Form\PostCommentType;
use App\PageView\PostCommentPageView;
use App\Repository\PostCommentRepository;
use App\Service\PostCommentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostCommentController extends AbstractController
{
    public function __construct(private PostCommentManager $manager)
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
        PostCommentRepository $repository
    ): Response {
        $dto = (new PostCommentDto())->createWithParent($post, $parent);

        $form = $this->getCreateForm($dto, $parent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleValidCreateRequest($dto, $request);
        }

        $criteria       = new PostCommentPageView($this->getPageNb($request));
        $criteria->post = $post;

        $comments = $repository->findByCriteria($criteria);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse($form, 'post/comment/_form.html.twig');
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

    private function getCreateForm(PostCommentDto $dto, ?PostComment $parent): FormInterface
    {
        return $this->createForm(
            PostCommentType::class,
            $dto,
            [
                'action' => $this->generateUrl(
                    'post_comment_create',
                    [
                        'magazine_name'     => $dto->post->magazine->name,
                        'post_id'           => $dto->post->getId(),
                        'parent_comment_id' => $parent?->getId(),
                    ]
                ),
            ]
        );
    }

    private function handleValidCreateRequest(PostCommentDto $dto, Request $request): Response
    {
        $comment = $this->manager->create($dto, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonCreateCommentSuccessResponse($comment);
        }

        return $this->redirectToRoute(
            'post_single',
            [
                'magazine_name' => $comment->magazine->name,
                'post_id'       => $comment->post->getId(),
            ]
        );
    }

    private function getJsonCreateCommentSuccessResponse(PostComment $comment): JsonResponse
    {
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
        PostCommentRepository $repository
    ): Response {
        $commentDto = $this->manager->createDto($comment);

        $form = $this->createForm(PostCommentType::class, $commentDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->edit($comment, $commentDto);

            return $this->redirectToPost($post);
        }

        $criteria       = new PostCommentPageView($this->getPageNb($request));
        $criteria->post = $post;

        $comments = $repository->findByCriteria($criteria);

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

        $this->manager->delete($comment, !$comment->isAuthor($this->getUserOrThrow()));

        return $this->redirectToMagazine($magazine);
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

        $this->manager->purge($comment);

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
