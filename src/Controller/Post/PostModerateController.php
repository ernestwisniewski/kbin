<?php

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Form\LangType;
use App\Repository\PostCommentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostModerateController extends AbstractController
{
    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('post', options: ['mapping' => ['post_id' => 'id']])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'post')]
    public function __invoke(
        Magazine $magazine,
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
