<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Post\Comment;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Form\LangType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostCommentModerateController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'comment')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        Request $request,
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
            ->setData($comment->lang);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('post/comment/_moderate_panel.html.twig', [
                    'magazine' => $magazine,
                    'post' => $post,
                    'comment' => $comment,
                    'form' => $form->createView(),
                ]),
            ]);
        }

        return $this->render('post/comment/moderate.html.twig', [
            'magazine' => $magazine,
            'post' => $post,
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }
}
