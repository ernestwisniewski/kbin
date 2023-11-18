<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Form\LangType;
use App\Repository\PostCommentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostModerateController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'post')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        Request $request,
        PostCommentRepository $repository
    ): Response {
        if ($post->magazine !== $magazine) {
            return $this->redirectToRoute(
                'post_single',
                ['magazine_name' => $post->magazine->name, 'post_id' => $post->getId(), 'slug' => $post->slug],
                301
            );
        }

        $form = $this->createForm(LangType::class);
        $form->get('lang')
            ->setData($post->lang);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('post/_moderate_panel.html.twig', [
                    'magazine' => $magazine,
                    'post' => $post,
                    'form' => $form->createView(),
                ]),
            ]);
        }

        return $this->render('post/moderate.html.twig', [
            'magazine' => $magazine,
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
}
