<?php declare(strict_types=1);

namespace App\Controller\Tag;

use App\Controller\AbstractController;
use App\PageView\EntryCommentPageView;
use App\Repository\EntryCommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentFrontController extends AbstractController
{
    public function __construct(
        private EntryCommentRepository $repository,
    ) {
    }

    public function __invoke(string $name, ?string $sortBy, ?string $time, Request $request): Response
    {
        $params   = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time))
            ->setTag($name);

        $params['comments'] = $this->repository->findByCriteria($criteria);
        $params['tag']        = $name;

        $this->repository->hydrate(...$params['comments']);
        $this->repository->hydrateChildren(...$params['comments']);

        return $this->render(
            'tag/comments.html.twig',
            $params
        );
    }
}
