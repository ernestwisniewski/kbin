<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TermsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteRepository $repository
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request): Response
    {
        $dto = $this->repository->findAll();
        if (!count($dto)) {
            $dto = new Site();
        } else {
            $dto = $dto[0];
        }

        $form = $this->createForm(SiteType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($dto);
            $this->entityManager->flush();

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render('admin/terms.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
