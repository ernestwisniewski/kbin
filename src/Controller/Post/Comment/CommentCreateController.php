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
use App\Service\CloudflareIpResolver;
use App\Service\PostCommentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CommentCreateController extends AbstractController
{
    use CommentResponseTrait;

    public function __construct(
        private PostCommentManager $manager,
        private PostCommentRepository $repository,
        private CloudflareIpResolver $ipResolver
    ) {
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
        $form = $this->getForm($post, $parent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto           = $form->getData();
            $dto->post     = $post;
            $dto->magazine = $magazine;
            $dto->parent   = $parent;
            $dto->ip       = $this->ipResolver->resolve();

            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            return $this->handleValidRequest($dto, $request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse($form, 'post/comment/_form.html.twig', ['post' => $post, 'parent' => $parent]);
        }

        $criteria       = new PostCommentPageView($this->getPageNb($request));
        $criteria->post = $post;

        $comments = $this->repository->findByCriteria($criteria);

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

    private function getForm(Post $post, ?PostComment $parent): FormInterface
    {
        return $this->createForm(
            PostCommentType::class,
            null,
            [
                'action' => $this->generateUrl(
                    'post_comment_create',
                    [
                        'magazine_name'     => $post->magazine->name,
                        'post_id'           => $post->getId(),
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
