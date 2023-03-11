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

class PostFavouriteController extends AbstractController
{
    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('post', options: ['mapping' => ['post_id' => 'id']])]
    public function __invoke(Magazine $magazine, Post $post, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('_layout/_voters_inline.html.twig', [
                    'votes' => $post->getUpVotes(),
                    'more' => null,
                ]),
            ]);
        }

        return $this->render('post/favourites.html.twig', [
            'magazine' => $magazine,
            'post' => $post,
            'favourites' => $post->favourites,
        ]);
    }
}
