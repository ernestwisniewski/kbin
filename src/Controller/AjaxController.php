<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Entry;
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

    public function fetchEntry(Entry $entry): JsonResponse
    {
        return new JsonResponse(
            [
                'id'   => $entry->getId(),
                'html' => $this->renderView('entry/__entry.html.twig', ['entry' => $entry, 'show_content' => true]),
            ]
        );
    }

    public function fetchEntryComment(EntryComment $comment): JsonResponse
    {
        return new JsonResponse(
            [
                'id'   => $comment->getId(),
                'html' => $this->renderView(
                    'entry/comment/_comment.html.twig',
                    [
                        'extra_classes' => 'kbin-comment',
                        'with_parent'   => false,
                        'comment'       => $comment,
                        'level'         => 1,
                        'nested'        => false,
                    ]
                ),
            ]
        );
    }

    public function fetchPost(Post $post): JsonResponse
    {
        return new JsonResponse(
            [
                'id'   => $post->getId(),
                'html' => $this->renderView('post/__post.html.twig', ['post' => $post]),
            ]
        );
    }

    public function fetchPostComment(PostComment $comment): JsonResponse
    {
        return new JsonResponse(
            [
                'id'   => $comment->getId(),
                'html' => $this->renderView(
                    'post/comment/_comment.html.twig',
                    [
                        'extra_classes' => 'kbin-comment',
                        'with_parent'   => false,
                        'comment'       => $comment,
                        'level'         => 1,
                        'nested'        => false,
                    ]
                ),
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
}
