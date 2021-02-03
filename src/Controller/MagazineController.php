<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pagerfanta\PagerfantaInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MagazineRepository;
use App\Repository\EntryRepository;
use App\Service\MagazineManager;
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

    public function front(Magazine $magazine, ?string $sortBy, Request $request): Response
    {
        $criteria = (new Criteria((int) $request->get('strona', 1)))->setMagazine($magazine);

        if ($sortBy) {
            $method = $criteria->translate($sortBy);
            $listing = $this->$method($criteria);
        } else {
            $listing = $this->new($criteria);
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
            $this->magazineManager->createMagazine($magazineDto, $this->getUserOrThrow());

            return $this->redirectToRoute('magazine', ['name' => $magazineDto->getName()]);
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
     * @IsGranted("edit", subject="magazine")
     */
    public function edit(Magazine $magazine, Request $request): Response
    {
        $magazineDto = $this->magazineManager->createMagazineDto($magazine);

        $form = $this->createForm(MagazineType::class, $magazineDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->magazineManager->editMagazine($magazine, $magazineDto);

            return $this->redirectToRoute(
                'magazine',
                [
                    'name' => $magazine->getName(),
                ]
            );
        }

        return $this->render(
            'magazine/edit.html.twig',
            [
                'magazine' => $magazine,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="magazine")
     */
    public function purge(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_purge', $request->request->get('token'));

        $this->magazineManager->purgeMagazine($magazine);

        return $this->redirectToRoute('front');
    }

    public function listAll(MagazineRepository $magazineRepository)
    {
        return $this->render(
            'magazine/list_all.html.twig',
            [
                'magazines' => $magazineRepository->findAll(),
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
