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
use App\Kbin\EntryComment\EntryCommentEdit;
use App\Kbin\EntryComment\Factory\EntryCommentFactory;
use App\PageView\EntryCommentPageView;
use App\Repository\EntryCommentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryCommentEditController extends AbstractController
{
    use EntryCommentResponseTrait;

    public function __construct(
        private readonly EntryCommentEdit $entryCommentEdit,
        private readonly EntryCommentFactory $entryCommentFactory,
        private readonly EntryCommentRepository $repository,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('edit', subject: 'comment')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        Request $request,
    ): Response {
        $dto = $this->entryCommentFactory->createDto($comment);

        $form = $this->getForm($dto, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            return $this->handleValidRequest($dto, $comment, $request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse(
                $form,
                'entry/comment/_form_comment.html.twig',
                ['comment' => $comment, 'entry' => $entry, 'edit' => true]
            );
        }

        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->entry = $entry;

        return $this->getEntryCommentPageResponse('entry/comment/edit.html.twig', $criteria, $form, $request, $comment);
    }

    private function getForm(EntryCommentDto $dto, EntryComment $comment): FormInterface
    {
        return $this->createForm(
            EntryCommentType::class,
            $dto,
            [
                'action' => $this->generateUrl(
                    'entry_comment_edit',
                    [
                        'magazine_name' => $comment->magazine->name,
                        'entry_id' => $comment->entry->getId(),
                        'comment_id' => $comment->getId(),
                    ]
                ),
            ]
        );
    }

    private function handleValidRequest(EntryCommentDto $dto, EntryComment $comment, Request $request): Response
    {
        $comment = ($this->entryCommentEdit)($comment, $dto);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonCommentSuccessResponse($comment);
        }

        return $this->redirectToEntry($comment->entry);
    }
}
