<?php

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\TwigComponent\ComponentAttributes;

class PostVotersController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'post_id')]
        Post $post,
        Request $request
    ): Response {
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
