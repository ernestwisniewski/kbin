<?php declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Magazine;
use App\PageView\EntryPageView;
use App\Repository\Criteria;
use App\Repository\EntryRepository;
use Pagerfanta\PagerfantaInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontController extends AbstractController
{
    public function __construct(private EntryRepository $repository)
    {
    }

    public function front(?string $sortBy, ?string $time, Request $request): Response
    {
        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($request->get('type')));

        $method  = $criteria->resolveSort($sortBy);
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
        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($request->get('type')));
        $criteria->subscribed = true;

        $method  = $criteria->resolveSort($sortBy);
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
    public function moderated(?string $sortBy, ?string $time, Request $request): Response
    {
        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($request->get('type', null)));
        $criteria->moderated = true;

        $method  = $criteria->resolveSort($sortBy);
        $listing = $this->$method($criteria);

        return $this->render(
            'front/front.html.twig',
            [
                'entries' => $listing,
            ]
        );
    }

    public function magazine(Magazine $magazine, ?string $sortBy, ?string $time, Request $request): Response
    {
        $criteria = (new EntryPageView($this->getPageNb($request)));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($request->get('type')));
        $criteria->magazine      = $magazine;
        $criteria->stickiesFirst = true;

        $method  = $criteria->resolveSort($sortBy);
        $listing = $this->$method($criteria);

        return $this->render(
            'magazine/front.html.twig',
            [
                'magazine' => $magazine,
                'entries'  => $listing,
            ]
        );
    }

    private function hot(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_HOT));
    }

    private function top(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_TOP));
    }

    private function active(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_ACTIVE));
    }

    private function newest(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_NEW));
    }

    private function commented(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_COMMENTED));
    }
}
