<?php

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\PageView\EntryPageView;
use App\PageView\MagazinePageView;
use App\Repository\MagazineRepository;
use App\Service\SearchManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineListController extends AbstractController
{
    public function __construct(private readonly SearchManager $searchManager)
    {
    }

    public function __invoke(?string $sortBy, ?string $view, MagazineRepository $repository, Request $request): Response
    {

        if ($q = $request->query->get('q')) {
            $magazines = $this->searchManager->findMagazinesPaginated($q, $request->query->getInt('p', 1));
        } else {
            $magazines = $repository->findAllPaginated($this->getPageNb($request), (new MagazinePageView(1))->resolveSort($sortBy));
        }

        return $this->render(
            'magazine/list_all.html.twig',
            [
                'magazines' => $magazines,
                'view' => $view,
                'q' => $q ?? '',
            ]
        );
    }
}
