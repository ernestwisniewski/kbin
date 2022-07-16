<?php declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Factory\ActivityPub\PostCommentNoteFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostCommentController
{
    public function __construct(private PostCommentNoteFactory $commentNoteFactory)
    {
    }

    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('post', options: ['mapping' => ['post_id' => 'id']])]
    #[ParamConverter('comment', options: ['mapping' => ['comment_id' => 'id']])]
    public function __invoke(
        Magazine $magazine,
        Post $post,
        PostComment $comment,
        Request $request
    ): Response {
        $response = new JsonResponse($this->commentNoteFactory->create($comment));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
