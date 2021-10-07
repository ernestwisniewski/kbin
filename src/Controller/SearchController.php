<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\SearchManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    public function __construct(private SearchManager $manager)
    {
    }

    public function __invoke(Request $request): Response
    {
        return $this->render(
            'search/front.html.twig',
            ['results' => $this->manager->findPaginated($request->query->get('q'), $this->getPageNb($request))]
        );
    }
}
