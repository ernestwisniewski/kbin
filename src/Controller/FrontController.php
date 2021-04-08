<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\PagerfantaInterface;
use App\Repository\EntryRepository;
use App\PageView\EntryPageView;
use App\Repository\Criteria;

class FrontController extends AbstractController
{
    public function __construct(private EntryRepository $entryRepository)
    {
    }

    public function front(?string $sortBy, ?string $time, Request $request): Response
    {
        $criteria = new EntryPageView((int) $request->get('strona', 1));
        $criteria->setTime($criteria->translateTime($time));
        $criteria->setType($criteria->translateType($request->get('typ', null)));

        $method  = $criteria->translateSort($sortBy);
        $listing = $this->$method($criteria);

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
    public function subscribed(?string $sortBy, ?string $time, Request $request): Response
    {
        $criteria             = new EntryPageView((int) $request->get('strona', 1));
        $criteria->subscribed = true;
        $criteria->setTime($criteria->translateTime($time));
        $criteria->setType($criteria->translateType($request->get('typ', null)));

        $method  = $criteria->translateSort($sortBy);
        $listing = $this->$method($criteria);

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
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_ACTIVE));
    }

    private function new(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria);
    }

    private function commented(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_COMMENTED));
    }
}
