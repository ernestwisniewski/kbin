<?php

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Form\LangType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryModerateController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'entry')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        if ($entry->magazine !== $magazine) {
            return $this->redirectToRoute(
                'entry_single',
                ['magazine_name' => $entry->magazine->name, 'entry_id' => $entry->getId(), 'slug' => $entry->slug],
                301
            );
        }

        $form = $this->createForm(LangType::class);
        //        $form->get('lang')->setData(['lang' => $entry->lang]);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('entry/_moderate_panel.html.twig', [
                    'magazine' => $magazine,
                    'entry' => $entry,
                    'form' => $form->createView(),
                ]),
            ]);
        }

        return $this->render('entry/moderate.html.twig', [
            'magazine' => $magazine,
            'entry' => $entry,
            'form' => $form->createView(),
        ]);
    }
}
