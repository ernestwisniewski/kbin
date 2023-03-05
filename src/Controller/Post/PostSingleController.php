<?php

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Controller\Traits\PrivateContentTrait;
use App\Controller\User\ThemeSettingsController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Event\Post\PostHasBeenSeenEvent;
use App\Form\PostCommentType;
use App\PageView\PostCommentPageView;
use App\Repository\Criteria;
use App\Repository\PostCommentRepository;
use Pagerfanta\PagerfantaInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostSingleController extends AbstractController
{
    use PrivateContentTrait;

    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('post', options: ['mapping' => ['post_id' => 'id']])]
    public function __invoke(
        Magazine $magazine,
        Post $post,
        ?string $sortBy,
        PostCommentRepository $repository,
        EventDispatcherInterface $dispatcher,
        Request $request
    ): Response {
        if ($post->magazine !== $magazine) {
            return $this->redirectToRoute(
                'post_single',
                ['magazine_name' => $post->magazine->name, 'post_id' => $post->getId(), 'slug' => $post->slug],
                301
            );
        }

        $this->handlePrivateContent($post);

        $criteria = new PostCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy));
        $criteria->post = $post;
        $criteria->onlyParents = true;
        $criteria->perPage = 25;

        if (ThemeSettingsController::CHAT === $request->cookies->get(
            ThemeSettingsController::POST_COMMENTS_VIEW
        )) {
            $criteria->showSortOption(Criteria::SORT_OLD);
            $criteria->perPage = 100;
            $criteria->onlyParents = false;
        }

        $comments = $repository->findByCriteria($criteria);

        $dispatcher->dispatch(new PostHasBeenSeenEvent($post));

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($magazine, $post, $comments);
        }

        return $this->render(
            'post/single.html.twig',
            [
                'magazine' => $magazine,
                'post' => $post,
                'comments' => $comments,
                'form' => $this->createForm(PostCommentType::class)->createView(),
            ]
        );
    }

    private function getJsonResponse(Magazine $magazine, Post $post, PagerfantaInterface $comments): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'post/_single_popup.html.twig',
                    [
                        'magazine' => $magazine,
                        'post' => $post,
                        'comments' => $comments,
                    ]
                ),
            ]
        );
    }
}
