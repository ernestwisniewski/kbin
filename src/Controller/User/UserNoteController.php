<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Kbin\User\Form\UserNoteType;
use App\Kbin\UserNote\Factory\UserNoteFactory;
use App\Kbin\UserNote\UserNoteClear;
use App\Kbin\UserNote\UserNoteSave;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserNoteController extends AbstractController
{
    public function __construct(
        private readonly UserNoteSave $userNoteSave,
        private readonly UserNoteClear $userNoteClear,
        private readonly UserNoteFactory $userNoteFactory
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(User $user, Request $request): Response
    {
        $dto = $this->userNoteFactory->createDto($this->getUserOrThrow(), $user);

        $form = $this->createForm(UserNoteType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            if ($dto->body) {
                ($this->userNoteSave)($this->getUserOrThrow(), $user, $dto->body);
            } else {
                ($this->userNoteClear)($this->getUserOrThrow(), $user);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonSuccessResponse();
        }

        return $this->redirectToRefererOrHome($request);
    }
}
