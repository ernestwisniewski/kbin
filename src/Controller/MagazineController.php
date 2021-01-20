<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MagazineRepository;
use App\Service\MagazineManager;
use App\Form\MagazineType;
use App\Entity\Magazine;
use App\DTO\MagazineDto;

class MagazineController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function front(Magazine $magazine): Response
    {
        return $this->render(
            'magazine/front.html.twig',
            [
                'magazine' => $magazine,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function createMagazine(Request $request, MagazineManager $magazineManager): Response
    {
        $magazineDto = new MagazineDto();

        $form = $this->createForm(MagazineType::class, $magazineDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $magazineManager->createMagazine($magazineDto, $this->getUserOrThrow());
            $this->entityManager->flush();

            return $this->redirectToRoute('magazine', ['name' => $magazineDto->getName()]);
        }

        return $this->render(
            'magazine/create.html.twig',
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
                'magazines' => $magazineRepository->findBy([], null, 4),
            ]
        );
    }

}
