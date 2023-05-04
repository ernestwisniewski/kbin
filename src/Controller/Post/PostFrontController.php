<?php

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\DTO\PostDto;
use App\Entity\Magazine;
use App\Form\PostType;
use App\PageView\PostPageView;
use App\Repository\Criteria;
use App\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostFrontController extends AbstractController
{
    public function front(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation($request->cookies->get('kbin_federation', Criteria::AP_ALL))
            ->setTime($criteria->resolveTime($time));

        $posts = $repository->findByCriteria($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'post/_list.html.twig',
                        [
                            'posts' => $posts,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'post/front.html.twig',
            [
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->createView(),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function subscribed(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation($request->cookies->get('kbin_federation', Criteria::AP_ALL))
            ->setTime($criteria->resolveTime($time));
        $criteria->subscribed = true;

        $posts = $repository->findByCriteria($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'post/_list.html.twig',
                        [
                            'posts' => $posts,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'post/front.html.twig',
            [
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->createView(),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function moderated(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation($request->cookies->get('kbin_federation', Criteria::AP_ALL))
            ->setTime($criteria->resolveTime($time));
        $criteria->moderated = true;

        $posts = $repository->findByCriteria($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'post/_list.html.twig',
                        [
                            'posts' => $posts,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'post/front.html.twig',
            [
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->createView(),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function favourite(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation($request->cookies->get('kbin_federation', Criteria::AP_ALL))
            ->setTime($criteria->resolveTime($time));
        $criteria->favourite = true;

        $posts = $repository->findByCriteria($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'post/_list.html.twig',
                        [
                            'posts' => $posts,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'post/front.html.twig',
            [
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->createView(),
            ]
        );
    }

    public function magazine(
        Magazine $magazine,
        ?string $sortBy,
        ?string $time,
        PostRepository $repository,
        Request $request
    ): Response {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation($request->cookies->get('kbin_federation', Criteria::AP_ALL))
            ->setTime($criteria->resolveTime($time));
        $criteria->magazine = $magazine;

        $posts = $repository->findByCriteria($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'post/_list.html.twig',
                        [
                            'magazine' => $magazine,
                            'posts' => $posts,
                        ]
                    ),
                ]
            );
        }

        $dto = new PostDto();
        $dto->magazine = $magazine;

        return $this->render(
            'post/front.html.twig',
            [
                'magazine' => $magazine,
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->setData($dto)->createView(),
            ]
        );
    }
}
