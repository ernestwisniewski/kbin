<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\PostDto;
use App\Entity\Post;
use App\Form\PostType;
use App\PageView\PostCommentPageView;
use App\PageView\PostPageView;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Service\PostManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Magazine;

class PostController extends AbstractController
{
    public function __construct(
        private PostManager $postManager,
        private EntityManagerInterface $entityManager
    ) {
    }


    public function front(?string $sortBy, ?string $time, PostRepository $postRepository, Request $request): Response
    {
        $criteria = (new PostPageView((int) $request->get('strona', 1)));

        if ($sortBy) {
            $criteria->showSortOption($sortBy);
        }

        if ($time) {
            $criteria->setTime($criteria->translateTime($time));
        }

        $posts = $postRepository->findByCriteria($criteria);

        return $this->render(
            'post/front.html.twig',
            [
                'posts' => $posts,
            ]
        );
    }

    public function subscribed(?string $sortBy, ?string $time, PostRepository $postRepository, Request $request): Response
    {
        $criteria = (new PostPageView((int) $request->get('strona', 1)))->showSubscribed();

        if ($sortBy) {
            $criteria->showSortOption($sortBy);
        }

        if ($time) {
            $criteria->setTime($criteria->translateTime($time));
        }

        $posts = $postRepository->findByCriteria($criteria);

        return $this->render(
            'post/front.html.twig',
            [
                'posts' => $posts,
            ]
        );
    }

    public function magazine(Magazine $magazine, ?string $sortBy, ?string $time, PostRepository $postRepository, Request $request): Response
    {
        $criteria = (new PostPageView((int) $request->get('strona', 1)))->showMagazine($magazine);

        if ($sortBy) {
            $criteria->showSortOption($sortBy);
        }

        if ($time) {
            $criteria->setTime($criteria->translateTime($time));
        }

        $posts = $postRepository->findByCriteria($criteria);

        return $this->render(
            'post/front.html.twig',
            [
                'magazine' => $magazine,
                'posts'    => $posts,
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     */
    public function single(Magazine $magazine, Post $post, PostCommentRepository $commentRepository, Request $request): Response
    {
        $criteria = (new PostCommentPageView((int) $request->get('strona', 1)))->showPost($post);

        return $this->render(
            'post/single.html.twig',
            [
                'magazine' => $magazine,
                'post'     => $post,
                'comments' => $commentRepository->findByCriteria($criteria),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("create_content", subject="magazine")
     */
    public function create(Magazine $magazine, Request $request): Response
    {
        $postDto = (new PostDto())->create($magazine);

        $form = $this->createForm(PostType::class, $postDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $this->postManager->create($postDto, $this->getUserOrThrow());

            return $this->redirectToRoute(
                'post_single',
                [
                    'magazine_name' => $post->getMagazine()->getName(),
                    'post_id'       => $post->getId(),
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="post")
     */
    public function edit(Magazine $magazine, Post $post, Request $request, PostCommentRepository $commentRepository): Response
    {
        $postDto = $this->postManager->createDto($post);

        $form = $this->createForm(PostType::class, $postDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $this->postManager->edit($post, $postDto);

            return $this->redirectToRoute(
                'post_single',
                [
                    'magazine_name' => $magazine->getName(),
                    'post_id'       => $post->getId(),
                ]
            );
        }

        $criteria = (new PostCommentPageView((int) $request->get('strona', 1)))->showPost($post);

        return $this->render(
            'post/edit.html.twig',
            [
                'magazine' => $magazine,
                'post'     => $post,
                'comments' => $commentRepository->findByCriteria($criteria),
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="post")
     */
    public function delete(Magazine $magazine, Post $post, Request $request): Response
    {
        $this->validateCsrf('post_delete', $request->request->get('token'));

        $this->postManager->delete($post, !$post->isAuthor($this->getUserOrThrow()));

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
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="post")
     */
    public function purge(Magazine $magazine, Post $post, Request $request): Response
    {
        $this->validateCsrf('post_purge', $request->request->get('token'));

        $this->postManager->purge($post);

        return $this->redirectToRoute(
            'front_magazine',
            [
                'name' => $magazine->getName(),
            ]
        );
    }

    public function postForm(string $magazineName): Response
    {
        $form = $this->createForm(PostType::class, null, ['action' => $this->generateUrl('post_create', ['name' => $magazineName])]);

        return $this->render(
            'post/_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
