<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\PageView\PostCommentPageView;
use App\Repository\PostCommentRepository;
use App\Utils\Embed;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends AbstractController
{
    public function fetchTitle(Embed $embed, Request $request): JsonResponse
    {
        $url = json_decode($request->getContent())->url;

        return new JsonResponse(
            [
                'title' => $embed->fetch($url)->title,
            ]
        );
    }

    public function fetchEmbed(Embed $embed, Request $request): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $embed->fetch($request->get('url'))->html,
            ]
        );
    }

    public function fetchPostComments(Post $post, PostCommentRepository $repository): JsonResponse
    {
        $criteria       = new PostCommentPageView(1);
        $criteria->post = $post;

        $comments = $repository->findByCriteria($criteria);

        return new JsonResponse(
            [
                'html' => $this->renderView('post/comment/_list.html.twig', ['comments' => $comments]),
            ]
        );
    }

    public function fetchEntryComment(Post $post, EntryComment $comment, PostCommentRepository $repository): JsonResponse
    {
        return new JsonResponse();
    }

    public function fetchPostComment(Post $post, PostComment $comment, PostCommentRepository $repository): JsonResponse
    {
        return new JsonResponse();
    }
}
