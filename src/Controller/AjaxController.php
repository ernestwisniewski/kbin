<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\PostCommentRepository;
use App\PageView\PostCommentPageView;
use App\Entity\Post;
use App\Utils\Embed;

class AjaxController extends AbstractController
{
    public function fetchTitle(Embed $embed, Request $request): JsonResponse
    {
        $url = json_decode($request->getContent())->url;

        return new JsonResponse(
            [
                'title' => $embed->fetch($url)->getTitle(),
            ]
        );
    }

    public function fetchEmbed(Embed $embed, Request $request): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $embed->fetch($request->get('url'))->getHtml(),
            ]
        );
    }

    public function fetchPostComments(Post $post, PostCommentRepository $commentRepository): JsonResponse
    {
        $criteria       = (new PostCommentPageView(1));
        $criteria->post = $post;

        $comments = $commentRepository->findByCriteria($criteria);

        return new JsonResponse(
            [
                'html' => $this->renderView('post/comment/_list.html.twig', ['comments' => $comments]),
            ]
        );
    }
}
