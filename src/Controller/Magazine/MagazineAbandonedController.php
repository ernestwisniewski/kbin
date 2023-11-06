<?php

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineAbandonedController extends AbstractController
{
    public function __construct(
        private readonly MagazineRepository $repository,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return $this->render(
            'magazine/list_abandoned.html.twig',
            [
                'magazines' => $this->repository->findAbandoned($request->query->getInt('p', 1)),
            ]
        );
    }
}
