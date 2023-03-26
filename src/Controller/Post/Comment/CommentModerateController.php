<?php

declare(strict_types=1);

namespace App\Controller\Post\Comment;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentModerateController extends AbstractController
{
    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('post', options: ['mapping' => ['post_id' => 'id']])]
    #[ParamConverter('comment', options: ['mapping' => ['comment_id' => 'id']])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'comment')]
    public function __invoke(
        Magazine $magazine,
        Post $post,
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

        return $this->render('post/comment/moderate.html.twig', [
            'magazine' => $magazine,
            'entry' => $post,
        ]);
    }
}
