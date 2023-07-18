<?php

declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Form\LangType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryCommentModerateController extends AbstractController
{
    #[IsGranted('moderate', subject: 'comment')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
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
        $form->get('lang')
            ->setData($comment->lang);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('entry/comment/_moderate_panel.html.twig', [
                    'magazine' => $magazine,
                    'entry' => $entry,
                    'comment' => $comment,
                    'form' => $form->createView(),
                ]),
            ]);
        }

        return $this->render('entry/comment/moderate.html.twig', [
            'magazine' => $magazine,
            'entry' => $entry,
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }
}
