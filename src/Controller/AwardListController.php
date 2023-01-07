<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\AwardTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AwardListController extends AbstractController
{
    public function __construct(private readonly AwardTypeRepository $repository)
    {
    }

    public function __invoke(?string $category, Request $request): Response
    {
        return $this->render(
            'award/list_all.html.twig',
            [
                'category' => $category,
                'types' => $this->repository->findBy($category ? ['category' => $category] : [], ['count' => 'DESC']),
            ]
        );
    }
}
