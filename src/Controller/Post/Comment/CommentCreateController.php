<?php declare(strict_types = 1);

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

class CommentCreateController extends AbstractController
{
    use CommentResponseTrait;

    public function __construct(private PostCommentManager $manager, private PostCommentRepository $repository)
    {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     * @ParamConverter("parent", options={"mapping": {"parent_comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("comment", subject="post")
     */
    public function __invoke(
        Magazine $magazine,
        Post $post,
        ?PostComment $parent,
        Request $request,
    ): Response {
        $dto           = (new PostCommentDto())->createWithParent($post, $parent);
        $dto->magazine = $magazine;
        $dto->ip       = $request->getClientIp();

        $form = $this->getCreateForm($dto, $parent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleValidRequest($dto, $request);
        }

        $criteria       = new PostCommentPageView($this->getPageNb($request));
        $criteria->post = $post;

        $comments = $this->repository->findByCriteria($criteria);

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
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
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

    private function handleValidRequest(PostCommentDto $dto, Request $request): Response
    {
        $comment = $this->manager->create($dto, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return $this->getPostCommentJsonSuccessResponse($comment);
        }

        return $this->redirectToPost($comment->post);
    }
}
