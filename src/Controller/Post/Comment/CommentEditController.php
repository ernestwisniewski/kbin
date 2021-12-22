<?php declare(strict_types=1);

namespace App\Controller\Post\Comment;

use App\Controller\AbstractController;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentEditController extends AbstractController
{
    use CommentResponseTrait;

    public function __construct(private PostCommentManager $manager, private PostCommentRepository $repository)
    {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="comment")
     */
    public function __invoke(
        Magazine $magazine,
        Post $post,
        PostComment $comment,
        Request $request,
    ): Response {
        $dto = $this->manager->createDto($comment);

        $form = $this->getCreateForm($dto, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleValidRequest($dto, $comment, $request);
        }

        $criteria = new PostCommentPageView($this->getPageNb($request));
        $criteria->post = $post;

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse($form, 'post/comment/_form.html.twig', ['comment' => $comment]);
        }

        $comments = $this->repository->findByCriteria($criteria);

        return $this->render(
            'post/comment/edit.html.twig',
            [
                'magazine' => $post->magazine,
                'post'     => $post,
                'comments' => $comments,
                'comment'  => $comment,
                'form'     => $form->createView(),
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
                    ['magazine_name' => $comment->magazine->name, 'post_id' => $comment->post->getId(), 'comment_id' => $comment->getId()]
                ),
            ]
        );
    }

    private function handleValidRequest(PostCommentDto $dto, PostComment $comment, Request $request): Response
    {
        $comment = $this->manager->edit($comment, $dto);

        if ($request->isXmlHttpRequest()) {
            return $this->getPostCommentJsonSuccessResponse($comment);
        }

        return $this->redirectToPost($comment->post);
    }
}
