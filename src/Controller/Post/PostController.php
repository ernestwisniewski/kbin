<?php declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\DTO\PostDto;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Event\Post\PostHasBeenSeenEvent;
use App\Form\PostType;
use App\PageView\PostCommentPageView;
use App\PageView\PostPageView;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Service\PostManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends AbstractController
{
    public function __construct(
        private PostManager $manager,
    ) {
    }

    public function front(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->translateSort($sortBy))
            ->setTime($criteria->translateTime($time));

        $posts = $repository->findByCriteria($criteria);

        return $this->render(
            'post/front.html.twig',
            [
                'posts' => $posts,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function subscribed(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->translateSort($sortBy))
            ->setTime($criteria->translateTime($time));
        $criteria->subscribed = true;

        $posts = $repository->findByCriteria($criteria);

        return $this->render(
            'post/front.html.twig',
            [
                'posts' => $posts,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function moderated(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->translateSort($sortBy))
            ->setTime($criteria->translateTime($time));
        $criteria->moderated = true;

        $posts = $repository->findByCriteria($criteria);

        return $this->render(
            'post/front.html.twig',
            [
                'posts' => $posts,
            ]
        );
    }

    public function magazine(Magazine $magazine, ?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->translateSort($sortBy))
            ->setTime($criteria->translateTime($time));
        $criteria->magazine = $magazine;

        $posts = $repository->findByCriteria($criteria);

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
    public function single(
        Magazine $magazine,
        Post $post,
        PostCommentRepository $repository,
        EventDispatcherInterface $dispatcher,
        Request $request
    ): Response {
        $criteria       = new PostCommentPageView($this->getPageNb($request));
        $criteria->post = $post;

        $dispatcher->dispatch((new PostHasBeenSeenEvent($post)));

        return $this->render(
            'post/single.html.twig',
            [
                'magazine' => $magazine,
                'post'     => $post,
                'comments' => $repository->findByCriteria($criteria),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("create_content", subject="magazine")
     */
    public function create(Magazine $magazine, Request $request): Response
    {
        $postDto = (new PostDto())->create($magazine, $this->getUserOrThrow(), null);

        $form = $this->createForm(PostType::class, $postDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $this->manager->create($postDto, $this->getUserOrThrow());

            return $this->redirectToRoute(
                'post_single',
                [
                    'magazine_name' => $post->magazine->name,
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
    public function edit(Magazine $magazine, Post $post, Request $request, PostCommentRepository $repository): Response
    {
        $postDto = $this->manager->createDto($post);

        $form = $this->createForm(PostType::class, $postDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $this->manager->edit($post, $postDto);

            return $this->redirectToRoute(
                'post_single',
                [
                    'magazine_name' => $magazine->name,
                    'post_id'       => $post->getId(),
                ]
            );
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

        $this->manager->delete($this->getUserOrThrow(), $post);

        return $this->redirectToRefererOrHome($request);
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

        $this->manager->purge($post);

        return $this->redirectToMagazine($magazine);
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
