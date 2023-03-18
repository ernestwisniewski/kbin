<?php

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\TwigComponent\ComponentAttributes;

class PostVotersController extends AbstractController
{
    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('post', options: ['mapping' => ['post_id' => 'id']])]
    public function __invoke(Magazine $magazine, Post $post, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('components/voters_inline.html.twig', [
                    'voters' => $post->getUpVotes()->map(fn ($vote) => $vote->user->username),
                    'attributes' => new ComponentAttributes([]),
                    'count' => 0,
                ]),
            ]);
        }

        return $this->render('post/voters.html.twig', [
            'magazine' => $magazine,
            'post' => $post,
            'votes' => $post->getUpVotes(),
        ]);
    }
}
