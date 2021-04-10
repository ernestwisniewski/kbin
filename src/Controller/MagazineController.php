<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MagazineRepository;
use App\Repository\EntryRepository;
use Pagerfanta\PagerfantaInterface;
use App\Service\MagazineManager;
use App\PageView\EntryPageView;
use App\Repository\Criteria;
use App\Form\MagazineType;
use App\Entity\Magazine;
use App\DTO\MagazineDto;

class MagazineController extends AbstractController
{
    public function __construct(
        private MagazineManager $manager,
        private EntryRepository $repository,
    ) {
    }

    public function front(Magazine $magazine, ?string $sortBy, ?string $time, Request $request): Response
    {
        $criteria = (new EntryPageView($this->getPageNb($request)));
        $criteria->showSortOption($criteria->translateSort($sortBy))
            ->setTime($criteria->translateTime($time))
            ->setType($criteria->translateType($request->get('typ', null)));
        $criteria->magazine      = $magazine;
        $criteria->stickiesFirst = true;

        $method  = $criteria->translateSort($sortBy);
        $listing = $this->$method($criteria);

        return $this->render(
            'magazine/front.html.twig',
            [
                'magazine' => $magazine,
                'entries'  => $listing,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request): Response
    {
        $dto = new MagazineDto();

        $form = $this->createForm(MagazineType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $magazine = $this->manager->create($dto, $this->getUserOrThrow());

            return $this->redirectToMagazine($magazine);
        }

        return $this->render(
            'magazine/create.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="magazine")
     */
    public function delete(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_delete', $request->request->get('token'));

        $this->manager->delete($magazine);

        return $this->redirectToRoute('front');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="magazine")
     */
    public function purge(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_purge', $request->request->get('token'));

        $this->manager->purge($magazine);

        return $this->redirectToRoute('front');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("subscribe", subject="magazine")
     */
    public function subscribe(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('subscribe', $request->request->get('token'));

        $this->manager->subscribe($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $magazine->subscriptionsCount,
                    'isSubscribed' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("subscribe", subject="magazine")
     */
    public function unsubscribe(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('subscribe', $request->request->get('token'));

        $this->manager->unsubscribe($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $magazine->subscriptionsCount,
                    'isSubscribed' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("block", subject="magazine")
     */
    public function block(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        $this->manager->block($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBlocked' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("block", subject="magazine")
     */
    public function unblock(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        $this->manager->unblock($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBlocked' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    public function listAll(MagazineRepository $magazineRepository, Request $request)
    {
        return $this->render(
            'magazine/list_all.html.twig',
            [
                'magazines' => $magazineRepository->findAllPaginated($this->getPageNb($request)),
            ]
        );
    }

    public function moderators(Magazine $magazine, MagazineRepository $magazineRepository, Request $request): Response
    {
        $page = $this->getPageNb($request);

        return $this->render(
            'magazine/moderators.html.twig',
            [
                'magazine'   => $magazine,
                'moderators' => $magazineRepository->findModerators($magazine, (int) $request->get('strona', $page)),
            ]
        );
    }

    public function modlog(Magazine $magazine, MagazineRepository $magazineRepository, Request $request): Response
    {
        $page = $this->getPageNb($request);

        return $this->render(
            'magazine/modlog.html.twig',
            [
                'magazine' => $magazine,
                'logs'     => $magazineRepository->findModlog($magazine, (int) $request->get('strona', $page)),
            ]
        );
    }

    public function featuredList(?Magazine $magazine, MagazineRepository $magazineRepository): Response
    {
        $magazines = $magazineRepository->findBy([], null, 20);

        if ($magazine && !in_array($magazine, $magazines)) {
            array_unshift($magazines, $magazine);
        }

        return $this->render(
            'magazine/_featured.html.twig',
            [
                'magazine'  => $magazine,
                'magazines' => $magazines,
            ]
        );
    }

    private function hot(Criteria $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_HOT));
    }

    private function top(Criteria $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_TOP));
    }

    private function active(Criteria $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_ACTIVE));
    }

    private function new(Criteria $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria);
    }

    private function commented(Criteria $criteria)
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_COMMENTED));
    }
}
