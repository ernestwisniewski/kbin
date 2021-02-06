<?php declare(strict_types = 1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pagerfanta\PagerfantaInterface;
use App\Repository\EntryRepository;
use App\Repository\Criteria;

class FrontController extends AbstractController
{
    private EntryRepository $entryRepository;

    public function __construct(EntryRepository $entryRepository)
    {
        $this->entryRepository = $entryRepository;
    }

    public function front(?string $sortBy, Request $request): Response
    {
        $criteria = new Criteria((int) $request->get('strona', 1));

        if ($sortBy) {
            $method = $criteria->translate($sortBy);
            $listing = $this->$method($criteria);
        } else {
            $listing = $this->new($criteria);
        }

        return $this->render(
            'front/front.html.twig',
            [
                'entries' => $listing,
            ]
        );
    }

    public function subscribed(?string $sortBy, Request $request): Response
    {
        $criteria = new Criteria((int) $request->get('strona', 1));

        if ($sortBy) {
            $method = $criteria->translate($sortBy);
            $listing = $this->$method($criteria);
        } else {
            $listing = $this->new($criteria);
        }

        return $this->render(
            'front/front.html.twig',
            [
                'entries' => $listing,
            ]
        );
    }

    private function hot(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->setSortOption(Criteria::SORT_HOT));
    }

    private function new(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria);
    }

    private function top(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->setSortOption(Criteria::SORT_HOT));
    }

    private function commented(Criteria $criteria)
    {
        return $this->entryRepository->findByCriteria($criteria->setSortOption(Criteria::SORT_COMMENTED));
    }
}
