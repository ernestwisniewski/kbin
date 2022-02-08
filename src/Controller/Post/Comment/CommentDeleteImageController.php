<?php declare(strict_types=1);

namespace App\Controller\Post\Comment;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Service\PostCommentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentDeleteImageController extends AbstractController
{
    public function __construct(
        private PostCommentManager $manager
    ) {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="comment")
     */
    public function __invoke(Magazine $magazine, Post $post, PostComment $comment, Request $request): Response
    {
        $this->manager->detachImage($comment);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonSuccessResponse();
        }

        return $this->redirectToRefererOrHome($request);
    }
}
