<?php declare(strict_types = 1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\PageView\PostPageView;
use App\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostFrontController extends AbstractController
{
    public function front(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time));

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
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time));
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
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time));
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
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time));
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
}
