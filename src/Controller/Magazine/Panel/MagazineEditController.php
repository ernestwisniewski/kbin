<?php

declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Kbin\Magazine\Form\MagazineType;
use App\Kbin\Magazine\MagazineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineEditController extends AbstractController
{
    public function __construct(
        private readonly MagazineEdit $magazineEdit,
        private readonly MagazineFactory $magazineFactory
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('edit', subject: 'magazine')]
    public function __invoke(Magazine $magazine, Request $request): Response
    {
        $magazineDto = $this->magazineFactory->createDto($magazine);

        $form = $this->createForm(MagazineType::class, $magazineDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ($this->magazineEdit)($magazine, $magazineDto);

            $this->addFlash(
                'success',
                'flash_magazine_edit_success'
            );

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'magazine/panel/general.html.twig',
            [
                'magazine' => $magazine,
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
