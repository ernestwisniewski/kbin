<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Controller\Traits\PrivateContentTrait;
use App\Controller\User\ThemeSettingsController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Kbin\Entry\EventSubscriber\Event\EntryHasBeenSeenEvent;
use App\Kbin\Entry\Form\EntryCommentType;
use App\Kbin\EntryComment\DTO\EntryCommentDto;
use App\Kbin\EntryComment\EntryCommentPageView;
use App\Kbin\NewCommentMarker\NewCommentMarkerViewSubject;
use App\Repository\Criteria;
use App\Repository\EntryCommentRepository;
use App\Service\MentionManager;
use Pagerfanta\PagerfantaInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntrySingleController extends AbstractController
{
    use PrivateContentTrait;

    public function __construct(
        private EntryCommentRepository $repository,
        private EventDispatcherInterface $dispatcher,
        private MentionManager $mentionManager,
        private NewCommentMarkerViewSubject $newCommentMarkerViewSubject
    ) {
    }

    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        ?string $sortBy,
        Request $request
    ): Response {
        if ($entry->magazine !== $magazine) {
            return $this->redirectToRoute(
                'entry_single',
                ['magazine_name' => $entry->magazine->name, 'entry_id' => $entry->getId(), 'slug' => $entry->slug],
                301
            );
        }

        $response = new Response();
        if ($entry->apId && $entry->user->apId) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        $this->handlePrivateContent($entry);

        if ($this->getUser()) {
            ($this->newCommentMarkerViewSubject)($this->getUser(), $entry);
        }

        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy));
        $criteria->entry = $entry;

        if (ThemeSettingsController::CHAT === $request->cookies->get(
            ThemeSettingsController::ENTRY_COMMENTS_VIEW
        )) {
            $criteria->showSortOption(Criteria::SORT_OLD);
            $criteria->perPage = 100;
            $criteria->onlyParents = false;
        }

        $comments = $this->repository->findByCriteria($criteria);

        $this->dispatcher->dispatch(new EntryHasBeenSeenEvent($entry));

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($magazine, $entry, $comments);
        }

        $dto = new EntryCommentDto();
        if ($this->getUser() && $this->getUser()->addMentionsEntries && $entry->user !== $this->getUser()) {
            $dto->body = $this->mentionManager->addHandle([$entry->user->username])[0];
        }

        return $this->render(
            'entry/single.html.twig',
            [
                'magazine' => $magazine,
                'comments' => $comments,
                'entry' => $entry,
                'form' => $this->createForm(EntryCommentType::class, $dto, [
                    'action' => $this->generateUrl(
                        'entry_comment_create',
                        [
                            'magazine_name' => $entry->magazine->name,
                            'entry_id' => $entry->getId(),
                        ]
                    ),
                    'parentLanguage' => $entry->lang,
                ])->createView(),
            ],
            $response
        );
    }

    private function getJsonResponse(Magazine $magazine, Entry $entry, PagerfantaInterface $comments): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'entry/_single_popup.html.twig',
                    [
                        'magazine' => $magazine,
                        'comments' => $comments,
                        'entry' => $entry,
                    ]
                ),
            ]
        );
    }
}
