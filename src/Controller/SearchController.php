<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\SearchRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    public function __construct(private SearchRepository $repo)
    {
    }

    public function __invoke(string $val, Request $request): Response
    {
        return $this->render('search/front.html.twig', ['results' => $this->repo->search($val)]);
    }
}
