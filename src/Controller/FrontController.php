<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pagerfanta\PagerfantaInterface;
use App\Repository\EntryRepository;
use App\PageView\EntryPageView;
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
        $criteria = new EntryPageView((int) $request->get('strona', 1));

        if ($sortBy) {
            $method  = $criteria->translate($sortBy);
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

    /**
     * @IsGranted("ROLE_USER")
     */
    public function subscribed(?string $sortBy, Request $request): Response
    {
        $criteria = new EntryPageView((int) $request->get('strona', 1));

        $criteria->showSubscribed();

        if ($sortBy) {
            $method  = $criteria->translate($sortBy);
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
        return $this->entryRepository->findByCriteria($criteria);
    }

    private function new(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria);
    }

    private function commented(EntryPageView $criteria)
    {
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_COMMENTED));
    }
}
