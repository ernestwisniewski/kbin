<?php declare(strict_types=1);

namespace App\Controller;

use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
            $sortBy  = $criteria->translate($sortBy);
            $listing = $this->$sortBy($criteria);
        } else {
            $listing = $this->all($criteria);
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
            $sortBy  = $criteria->translate($sortBy);
            $listing = $this->$sortBy($criteria);
        } else {
            $listing = $this->all($criteria);
        }

        return $this->render(
            'front/front.html.twig',
            [
                'entries' => $listing,
            ]
        );
    }

    private function all(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->orderBy(Criteria::SORT_HOT));
    }

    private function hot(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->orderBy(Criteria::SORT_HOT));
    }

    private function new(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria);
    }

    private function top(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->orderBy(Criteria::SORT_HOT));
    }

    private function commented(Criteria $criteria)
    {
        return $this->entryRepository->findByCriteria($criteria->orderBy(Criteria::SORT_COMMENTED));
    }
}
