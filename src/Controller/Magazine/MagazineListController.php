<?php declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Repository\MagazineRepository;
use App\Service\SearchManager;
use Symfony\Component\HttpFoundation\Request;

class MagazineListController extends AbstractController
{
    public function __construct(private SearchManager $searchManager)
    {
    }

    public function __invoke(MagazineRepository $repository, Request $request)
    {
        if ($q = $request->get('q')) {
            $magazines = $this->searchManager->findMagazinesPaginated($q);
        } else {
            $magazines = $repository->findAllPaginated($this->getPageNb($request));
        }

        return $this->render(
            'magazine/list_all.html.twig',
            [
                'magazines' => $magazines,
            ]
        );
    }
}
