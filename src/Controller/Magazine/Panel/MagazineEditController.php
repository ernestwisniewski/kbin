<?php declare(strict_types = 1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Form\MagazineType;
use App\Service\MagazineManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineEditController extends AbstractController
{
    public function __construct(
        private MagazineManager $manager,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="magazine")
     */
    public function __invoke(Magazine $magazine, Request $request): Response
    {
        $magazineDto = $this->manager->createDto($magazine);

        $form = $this->createForm(MagazineType::class, $magazineDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->edit($magazine, $magazineDto);

            $this->addFlash(
                'success',
                'flash_magazine_edit_success'
            );

            return $this->redirectToMagazine($magazine);
        }

        return $this->render(
            'magazine/panel/edit.html.twig',
            [
                'magazine' => $magazine,
                'form'     => $form->createView(),
            ]
        );
    }
}
