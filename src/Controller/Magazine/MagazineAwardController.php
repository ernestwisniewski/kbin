<?php

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineAwardController extends AbstractController
{
    public function __invoke(Magazine $magazine, ?string $category, Request $request): Response
    {
        return $this->render(
            'award/list_all.html.twig',
            [
                'magazine' => $magazine,
                'category' => $category,
                'types' => [],
            ]
        );
    }
}
