<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Kbin\MarkNewComment\MarkNewCommentViewSubject;
use App\Kbin\PostComment\PostCommentPageView;
use App\Kbin\User\DTO\UserNoteDto;
use App\Kbin\User\Form\UserNoteType;
use App\Repository\Criteria;
use App\Repository\EntryRepository;
use App\Repository\NotificationRepository;
use App\Repository\PostCommentRepository;
use App\Repository\UserRepository;
use App\Service\UserNoteManager;
use App\Utils\Embed;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AjaxController extends AbstractController
{
    public function __construct(private MarkNewCommentViewSubject $markNewCommentViewSubject)
    {
    }

    public function fetchTitle(Embed $embed, Request $request): JsonResponse
    {
        $url = json_decode($request->getContent())->url;
        $embed = $embed->fetch($url);

        return new JsonResponse(
            [
                'title' => $embed->title,
                'description' => $embed->description,
                'image' => $embed->image,
            ]
        );
    }

    public function fetchDuplicates(EntryRepository $repository, Request $request): JsonResponse
    {
        $url = json_decode($request->getContent())->url;
        $entries = $repository->findBy(['url' => $url]);

        return new JsonResponse(
            [
                'total' => \count($entries),
                'html' => $this->renderView('entry/_list.html.twig', ['entries' => $entries]),
            ]
        );
    }

    /**
     * Returns an embeded objects html value, to be used for front-end insertion.
     */
    public function fetchEmbed(Embed $embed, Request $request): JsonResponse
    {
        $data = $embed->fetch($request->get('url'));

        return new JsonResponse(
            [
                'html' => sprintf('<a href="%s" class="embed-link"><div class="preview">%s</div></a>', $data->url, $data->html),
            ]
        );
    }

    public function fetchEntry(Entry $entry, Request $request): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'components/_ajax.html.twig',
                    [
                        'component' => 'entry',
                        'attributes' => [
                            'entry' => $entry,
                        ],
                    ]
                ),
            ]
        );
    }

    public function fetchEntryComment(EntryComment $comment): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'components/_ajax.html.twig',
                    [
                        'component' => 'entry_comment',
                        'attributes' => [
                            'comment' => $comment,
                            'showEntryTitle' => false,
                            'showMagazineName' => false,
                        ],
                    ]
                ),
            ]
        );
    }

    public function fetchPost(Post $post): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'components/_ajax.html.twig',
                    [
                        'component' => 'post',
                        'attributes' => [
                            'post' => $post,
                        ],
                    ]
                ),
            ]
        );
    }

    public function fetchPostComment(PostComment $comment): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'components/_ajax.html.twig',
                    [
                        'component' => 'post_comment',
                        'attributes' => [
                            'comment' => $comment,
                        ],
                    ]
                ),
            ]
        );
    }

    public function fetchPostComments(Post $post, PostCommentRepository $repository): JsonResponse
    {
        if ($this->getUser()) {
            ($this->markNewCommentViewSubject)($this->getUser(), $post);
        }

        $criteria = new PostCommentPageView(1);
        $criteria->post = $post;
        $criteria->sortOption = Criteria::SORT_OLD;
        $criteria->perPage = 500;

        $comments = $repository->findByCriteria($criteria);

        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'post/comment/_preview.html.twig',
                    ['comments' => $comments, 'post' => $post]
                ),
            ]
        );
    }

    public function fetchOnline(
        string $topic,
        string $mercurePublicUrl,
        string $mercureSubscriptionsToken,
        HttpClientInterface $httpClient,
        CacheInterface $cache
    ): JsonResponse {
        $resp = $httpClient->request('GET', $mercurePublicUrl.'/subscriptions/'.$topic, [
            'auth_bearer' => $mercureSubscriptionsToken,
        ]);

        // @todo cloudflare bug
        $online = $cache->get($topic, function (ItemInterface $item) use ($resp) {
            $item->expiresAfter(45);

            return \count($resp->toArray()['subscriptions']) + 1;
        });

        return new JsonResponse([
            'online' => $online,
        ]);
    }

    public function fetchUserPopup(User $user, UserNoteManager $manager): JsonResponse
    {
        if ($this->getUser()) {
            $dto = $manager->createDto($this->getUserOrThrow(), $user);
        } else {
            $dto = new UserNoteDto();
            $dto->target = $user;
        }

        $form = $this->createForm(UserNoteType::class, $dto, [
            'action' => $this->generateUrl('user_note', ['username' => $dto->target->username]),
        ]);

        return new JsonResponse([
            'html' => $this->renderView('user/_user_popover.html.twig', ['user' => $user, 'form' => $form->createView()]
            ),
        ]);
    }

    public function fetchUsersSuggestions(string $username, Request $request, UserRepository $repository): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'user/_suggestion.html.twig',
                    [
                        'users' => $repository->findUsersSuggestions(ltrim($username, '@')),
                    ]
                ),
            ]
        );
    }

    public function fetchNotificationsCount(User $user, NotificationRepository $repository): JsonResponse
    {
        return new JsonResponse([
            'count' => $repository->countUnreadNotifications($user),
        ]);
    }
}
