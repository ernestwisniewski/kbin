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

class FrontController extends AbstractController
{
    public function __construct(private EntryRepository $entryRepository)
    {
    }

    public function __invoke(?string $name, ?string $sortBy, ?string $time, Request $request): Response
    {
        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($request->get('type', null)))
            ->setTag($name);
        $method  = $criteria->resolveSort($sortBy);
        $listing = $this->$method($criteria);

        return $this->render(
            'tag/front.html.twig',
            [
                'q'       => $name,
                'entries' => $listing,
            ]
        );
    }

    private function hot(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_HOT));
    }

    private function top(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_TOP));
    }

    private function active(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_ACTIVE));
    }

    private function newest(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_NEW));
    }

    private function commented(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_COMMENTED));
    }
}
