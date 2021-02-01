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
            $sortBy  = $this->translate($sortBy);
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

    public function all(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->orderBy(Criteria::SORT_HOT));
    }

    public function hot(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->orderBy(Criteria::SORT_HOT));
    }

    public function new(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria);
    }

    public function top(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->orderBy(Criteria::SORT_HOT));
    }

    public function commented(Criteria $criteria)
    {
        return $this->entryRepository->findByCriteria($criteria->orderBy(Criteria::SORT_COMMENTED));
    }

    private function translate(string $value)
    {
        //@todo
        $routes = [
            'wazne'       => Criteria::SORT_HOT,
            'najnowsze'   => Criteria::SORT_NEW,
            'wschodzace'  => Criteria::SORT_TOP,
            'komentowane' => Criteria::SORT_COMMENTED,
        ];

        return $routes[$value];
    }
}
