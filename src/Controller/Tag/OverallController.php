<?php declare(strict_types=1);

namespace App\Controller\Tag;

use App\Controller\AbstractController;
use App\PageView\EntryPageView;
use App\Repository\Criteria;
use App\Repository\EntryRepository;
use App\Repository\TagRepository;
use App\Service\SearchManager;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OverallController extends AbstractController
{
    public function __construct(private SearchManager $manager, private TagRepository $tagRepository)
    {
    }

    public function __invoke(string $name, Request $request): Response
    {
        return $this->render(
            'tag/overall.html.twig',
            ['tag' => $name, 'results' => $this->tagRepository->findOverall($this->getPageNb($request), $name)]
//            ['q' => '#'.$name, 'results' => $this->manager->findByTagPaginated($name, $this->getPageNb($request))]
        );
    }
}
