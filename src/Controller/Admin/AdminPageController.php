<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Form\PageType;
use App\Service\PageManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminPageController extends AbstractController
{
    public function __construct(
        private readonly PageManager $manager
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request, string $page = 'about'): Response
    {
        $form = $this->createForm(PageType::class, $this->manager->getDto($page));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->save($page, $form->getData());

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render('admin/pages.html.twig', [
            'form' => $form->createView(),
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}
