<?php

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Form\MagazineTagsType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineAddTagsController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Magazine $magazine, Request $request): Response
    {
        $form = $this->createForm(MagazineTagsType::class, $magazine);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $form->getData();
            $this->entityManager->flush();

            return $this->redirectToRefererOrHome($request);
        }

        return $this->renderForm('magazine/panel/tags.html.twig', [
            'magazine' => $magazine,
            'form' => $form,
        ]);
    }
}
