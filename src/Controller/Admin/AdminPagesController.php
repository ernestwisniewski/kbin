<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\DTO\PageDto;
use App\Entity\Site;
use App\Form\PageType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminPagesController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteRepository $repository
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request, ?string $page = 'about'): Response
    {
        $entity = $this->repository->findAll();
        if (!\count($entity)) {
            $entity = new Site();
        } else {
            $entity = $entity[0];
        }

        $form = $this->createForm(PageType::class, (new PageDto())->create($entity->{$page} ?? ''));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity->{$page} = $form->getData()->body;
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render('admin/pages.html.twig', [
            'form' => $form->createView(),
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}
