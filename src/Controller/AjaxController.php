<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\PageView\PostCommentPageView;
use App\Repository\Criteria;
use App\Repository\EntryRepository;
use App\Repository\PostCommentRepository;
use App\Utils\Embed;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

    public function fetchDuplicates(EntryRepository $repository, Request $request): JsonResponse
    {
        $url     = json_decode($request->getContent())->url;
        $entries = $repository->findBy(['url' => $url]);

        return new JsonResponse(
            [
                'entries' => [],
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
                'html' => $this->renderView('entry/_entry.html.twig', ['entry' => $entry]),
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
                        'extraClass' => 'kbin-comment',
                        'withParent' => false,
                        'comment'    => $comment,
                        'level'      => 1,
                        'nested'     => false,
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
                'html' => $this->renderView('post/_post.html.twig', ['post' => $post]),
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
        $criteria             = new PostCommentPageView(1);
        $criteria->post       = $post;
        $criteria->sortOption = Criteria::SORT_NEW;

        $comments = $repository->findByCriteria($criteria);

        return new JsonResponse(
            [
                'html' => $this->renderView('post/comment/_list.html.twig', ['comments' => $comments, 'post' => $post]),
            ]
        );
    }

    public function fetchOnline(
        string $topic,
        string $mercurePublishUrl,
        string $mercureSubscriptionsToken,
        HttpClientInterface $httpClient,
        CacheInterface $cache
    ): JsonResponse {
        $resp = $httpClient->request('GET', $mercurePublishUrl.'/subscriptions/'.$topic, [
            'auth_bearer' => $mercureSubscriptionsToken,
        ]);

        // @todo cloudflare bug
        $online = $cache->get($topic, function (ItemInterface $item) use ($resp) {
            $item->expiresAfter(45);

            return count($resp->toArray()['subscriptions']) + 1;
        });

        return new JsonResponse([
            'online' => $online,
        ]);
    }
}
