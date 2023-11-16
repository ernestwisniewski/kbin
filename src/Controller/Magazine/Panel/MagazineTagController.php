<?php

declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Form\MagazineTagsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineTagController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function __invoke(Magazine $magazine, Request $request): Response
    {
        $form = $this->createForm(MagazineTagsType::class, $magazine);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $form->getData();
            $this->entityManager->flush();

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render('magazine/panel/tags.html.twig', [
            'magazine' => $magazine,
            'form' => $form,
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}
