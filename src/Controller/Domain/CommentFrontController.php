<?php declare(strict_types=1);

namespace App\Controller\Domain;

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
            ->setDomain($name);

        $params['comments'] = $this->repository->findByCriteria($criteria);
        $params['domain']        = $name;

        $this->repository->hydrate(...$params['comments']);
        $this->repository->hydrateChildren(...$params['comments']);

        return $this->render(
            'domain/comments.html.twig',
            $params
        );
    }
}
