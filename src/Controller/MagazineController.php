<?php declare(strict_types = 1);

namespace App\Controller;

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
    /**
     * @var MagazineManager
     */
    private $magazineManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(MagazineManager $magazineManager, EntityManagerInterface $entityManager)
    {
        $this->magazineManager = $magazineManager;
        $this->entityManager = $entityManager;
    }

    public function front(Magazine $magazine, EntryRepository $entryRepository): Response
    {
        return $this->render(
            'magazine/front.html.twig',
            [
                'magazine' => $magazine,
                'entries'  => $entryRepository->findBy(['magazine' => $magazine]),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function createMagazine(Request $request): Response
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
     */
    public function editMagazine(Magazine $magazine, Request $request): Response
    {
        $magazineDto = $this->magazineManager->createMagazineDto($magazine);

        $form = $this->createForm(MagazineType::class, $magazineDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->magazineManager->editMagazine($magazine, $magazineDto);

            return $this->redirectToRoute('magazine', [
                'name' => $magazine->getName(),
            ]);
        }

        return $this->render(
            'magazine/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
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

    public function featuredMagazines(MagazineRepository $magazineRepository): Response
    {
        return $this->render(
            'magazine/_featured.html.twig',
            [
                'magazines' => $magazineRepository->findBy([], null, 30),
            ]
        );
    }

}
