<?php declare(strict_types = 1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Request;

class MagazineListController extends AbstractController
{
    public function __invoke(MagazineRepository $repository, Request $request)
    {
        return $this->render(
            'magazine/list_all.html.twig',
            [
                'magazines' => $repository->findAllPaginated($this->getPageNb($request)),
            ]
        );
    }
}
