<?php declare(strict_types=1);

namespace App\Controller\People;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontController extends AbstractController
{
    public function __invoke(?string $category, Request $request): Response
    {
        return $this->render(
            'people/front.html.twig',
        );
    }
}
