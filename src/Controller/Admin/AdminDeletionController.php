<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminDeletionController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MagazineRepository $magazineRepository,
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function users(Request $request): Response
    {
        return $this->render('admin/deletion_users.html.twig', [
            'users' => $this->userRepository->findForDeletionPaginated($request->get('page', 1)),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function magazines(Request $request): Response
    {
        return $this->render('admin/deletion_magazines.html.twig', [
            'magazines' => $this->magazineRepository->findForDeletionPaginated($request->get('page', 1)),
        ]);
    }
}
