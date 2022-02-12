<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\UserNoteDto;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Form\UserNoteType;
use App\PageView\PostCommentPageView;
use App\Repository\Criteria;
use App\Repository\EntryRepository;
use App\Repository\NotificationRepository;
use App\Repository\PostCommentRepository;
use App\Service\NotificationManager;
use App\Service\UserNoteManager;
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
                'total' => count($entries),
                'html'  => $this->renderView('entry/_list.html.twig', ['entries' => $entries]),
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

    public function fetchUserPopup(User $user, UserNoteManager $manager): JsonResponse
    {
        if($this->getUser()) {
            $dto = $manager->createDto($this->getUserOrThrow(), $user);
        } else {
            $dto = new UserNoteDto();
            $dto->target = $user;
        }

        $form = $this->createForm(UserNoteType::class, $dto, [
            'action' => $this->generateUrl('user_note', ['username' => $dto->target->username]),
        ]);

        return new JsonResponse([
            'html' => $this->renderView('user/user_popup.html.twig', ['user' => $user, 'form' => $form->createView()]),
        ]);
    }

    public function fetchNotificationsCount(User $user, NotificationRepository $repository): JsonResponse
    {
        return new JsonResponse([
            'count' => $repository->countUnreadNotifications($user)
        ]);
    }
}
