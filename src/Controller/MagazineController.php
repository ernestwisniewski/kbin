<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MagazineRepository;
use App\Service\MagazineManager;
use App\Form\MagazineType;
use App\DTO\MagazineDto;

class MagazineController extends AbstractController
{
    public function front(): Response
    {
        return $this->render(
            'magazine/front.html.twig',
            [
                'controller_name' => 'MagazineController',
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function createMagazine(Request $request, MagazineManager $magazineManager, EntityManagerInterface $entityManager): Response
    {
        $magazineDto = new MagazineDto();

        $form = $this->createForm(MagazineType::class, $magazineDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $magazineManager->create($magazineDto, $this->getUserOrThrow());
            $entityManager->flush();

            return $this->redirectToRoute('magazine_list_all');
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
}
