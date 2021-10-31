<?php declare(strict_types = 1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\PageView\EntryCommentPageView;
use App\Repository\EntryCommentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentFrontController extends AbstractController
{
    public function __construct(
        private EntryCommentRepository $repository,
    ) {
    }

    public function front(?Magazine $magazine, ?string $sortBy, ?string $time, Request $request): Response
    {
        $params   = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time));

        if ($magazine) {
            $criteria->magazine = $params['magazine'] = $magazine;
        }

        $params['comments'] = $this->repository->findByCriteria($criteria);

        $this->repository->hydrate(...$params['comments']);
        $this->repository->hydrateChildren(...$params['comments']);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function subscribed(?string $sortBy, ?string $time, Request $request): Response
    {
        $params   = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time));
        $criteria->subscribed = true;

        $params['comments'] = $this->repository->findByCriteria($criteria);

        $this->repository->hydrate(...$params['comments']);
        $this->repository->hydrateChildren(...$params['comments']);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function moderated(?string $sortBy, ?string $time, Request $request): Response
    {
        $params   = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time));
        $criteria->moderated = true;

        $params['comments'] = $this->repository->findByCriteria($criteria);

        $this->repository->hydrate(...$params['comments']);
        $this->repository->hydrateChildren(...$params['comments']);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }
}
