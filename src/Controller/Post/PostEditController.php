<?php declare(strict_types = 1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Form\PostType;
use App\PageView\PostCommentPageView;
use App\Repository\PostCommentRepository;
use App\Service\PostManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PostEditController extends AbstractController
{
    public function __construct(
        private PostManager $manager,
    ) {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="post")
     */
    public function __invoke(Magazine $magazine, Post $post, Request $request, PostCommentRepository $repository): Response
    {
        $dto = $this->manager->createDto($post);

        $form = $this->createForm(PostType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('create_content', $magazine)) {
                throw new AccessDeniedHttpException();
            }

            $post = $this->manager->edit($post, $dto);

            return $this->redirectToPost($post);
        }

        $criteria       = new PostCommentPageView($this->getPageNb($request));
        $criteria->post = $post;

        return $this->render(
            'post/edit.html.twig',
            [
                'magazine' => $magazine,
                'post'     => $post,
                'comments' => $repository->findByCriteria($criteria),
                'form'     => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
