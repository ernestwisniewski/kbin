<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\MagazineBanDto;
use App\Entity\User;
use App\Form\MagazineBanType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
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
    private MagazineManager $magazineManager;
    private EntryRepository $entryRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(MagazineManager $magazineManager, EntryRepository $entryRepository, EntityManagerInterface $entityManager)
    {
        $this->magazineManager = $magazineManager;
        $this->entryRepository = $entryRepository;
        $this->entityManager   = $entityManager;
    }

    public function front(Magazine $magazine, ?string $sortBy, ?string $time, Request $request): Response
    {
        $criteria = (new EntryPageView((int) $request->get('strona', 1)))->showMagazine($magazine);

        if ($time) {
            $criteria->setTime($criteria->translateTime($time));
        }

        if ($type = $request->get('typ', null)) {
            $criteria->setType($criteria->translateType($type));
        }

        if ($sortBy) {
            $method  = $criteria->translateSort($sortBy);
            $listing = $this->$method($criteria);
        } else {
            $listing = $this->active($criteria);
        }

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
        $magazineDto = new MagazineDto();

        $form = $this->createForm(MagazineType::class, $magazineDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->magazineManager->create($magazineDto, $this->getUserOrThrow());

            return $this->redirectToRoute('front_magazine', ['name' => $magazineDto->getName()]);
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

        $this->magazineManager->delete($magazine);

        return $this->redirectToRoute('front');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="magazine")
     */
    public function purge(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_purge', $request->request->get('token'));

        $this->magazineManager->purge($magazine);

        return $this->redirectToRoute('front');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("subscribe", subject="magazine")
     */
    public function subscribe(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('subscribe', $request->request->get('token'));

        $this->magazineManager->subscribe($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $magazine->getSubscriptionsCount(),
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

        $this->magazineManager->unsubscribe($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $magazine->getSubscriptionsCount(),
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

        $this->magazineManager->block($magazine, $this->getUserOrThrow());

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

        $this->magazineManager->unblock($magazine, $this->getUserOrThrow());

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
                'magazines' => $magazineRepository->findAllPaginated((int) $request->get('strona', 1)),
            ]
        );
    }

    public function moderators(Magazine $magazine, MagazineRepository $magazineRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

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
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'magazine/modlog.html.twig',
            [
                'magazine'   => $magazine,
                'logs' => $magazineRepository->findModlog($magazine, (int) $request->get('strona', $page)),
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
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_HOT));
    }

    private function top(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_TOP));
    }

    private function active(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_ACTIVE));
    }

    private function new(Criteria $criteria): PagerfantaInterface
    {
        return $this->entryRepository->findByCriteria($criteria);
    }

    private function commented(Criteria $criteria)
    {
        return $this->entryRepository->findByCriteria($criteria->showSortOption(Criteria::SORT_COMMENTED));
    }
}
