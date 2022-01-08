<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\SearchManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagController extends AbstractController
{
    public function __construct(private SearchManager $manager)
    {
    }

    public function __invoke(string $name, Request $request): Response
    {
        return $this->render(
            'search/front.html.twig',
            ['q' => '#'.$name, 'results' => $this->manager->findPaginated($name, $this->getPageNb($request))]
        );
    }
}
