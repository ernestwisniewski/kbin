<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Kbin\Entry\Form\EntryCommentType;
use App\Kbin\EntryComment\DTO\EntryCommentDto;
use App\Kbin\EntryComment\EntryCommentCreate;
use App\Kbin\EntryComment\EntryCommentPageView;
use App\Service\IpResolver;
use App\Service\MentionManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryCommentCreateController extends AbstractController
{
    use EntryCommentResponseTrait;

    public function __construct(
        private readonly EntryCommentCreate $entryCommentCreate,
        private readonly RequestStack $requestStack,
        private readonly IpResolver $ipResolver,
        private readonly MentionManager $mentionManager
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('comment', subject: 'entry')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        #[MapEntity(id: 'parent_comment_id')]
        ?EntryComment $parent,
        Request $request,
    ): Response {
        $form = $this->getForm($entry, $parent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();
            $dto->magazine = $magazine;
            $dto->entry = $entry;
            $dto->parent = $parent;
            $dto->ip = $this->ipResolver->resolve();

            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            return $this->handleValidRequest($dto, $request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse(
                $form,
                'entry/comment/_form_comment.html.twig',
                ['entry' => $entry, 'parent' => $parent]
            );
        }

        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->entry = $entry;

        return $this->getEntryCommentPageResponse(
            'entry/comment/create.html.twig',
            $criteria,
            $form,
            $request,
            $parent
        );
    }

    private function getForm(Entry $entry, EntryComment $parent = null): FormInterface
    {
        $dto = new EntryCommentDto();

        if ($parent && $this->getUser()->addMentionsEntries) {
            $handle = $this->mentionManager->addHandle([$parent->user->username])[0];

            if ($parent->user !== $this->getUser()) {
                $dto->body = $handle;
            } else {
                $dto->body .= PHP_EOL;
            }

            if ($parent->mentions) {
                $mentions = $this->mentionManager->addHandle($parent->mentions);
                $mentions = array_filter(
                    $mentions,
                    fn (string $mention) => $mention !== $handle && $mention !== $this->mentionManager->addHandle(
                        [$this->getUser()->username]
                    )[0]
                );

                $dto->body .= PHP_EOL.PHP_EOL;
                $dto->body .= implode(' ', array_unique($mentions));
            }
        }

        return $this->createForm(
            EntryCommentType::class,
            $dto,
            [
                'action' => $this->generateUrl(
                    'entry_comment_create',
                    [
                        'magazine_name' => $entry->magazine->name,
                        'entry_id' => $entry->getId(),
                        'parent_comment_id' => $parent?->getId(),
                    ]
                ),
                'parentLanguage' => $parent?->lang ?? $entry->lang,
            ]
        );
    }

    private function handleValidRequest(EntryCommentDto $dto, Request $request): Response
    {
        $comment = ($this->entryCommentCreate)($dto, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonCommentSuccessResponse($comment);
        }

        return $this->redirectToEntry($comment->entry);
    }
}
