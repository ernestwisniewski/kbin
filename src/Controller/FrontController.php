<?php declare(strict_types=1);

namespace App\Controller;

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
        $criteria->showSortOption($criteria->translateSort($sortBy))
            ->setTime($criteria->translateTime($time))
            ->setType($criteria->translateType($request->get('typ', null)));

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
        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->translateSort($sortBy))
            ->setTime($criteria->translateTime($time))
            ->setType($criteria->translateType($request->get('typ', null)));
        $criteria->subscribed = true;

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

    private function new(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria);
    }

    private function commented(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_COMMENTED));
    }
}
