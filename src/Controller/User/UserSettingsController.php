<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserSettingsController extends AbstractController
{
    public const KBIN_SUB_CHANNEL_USERS = 'showSubscribedUsers';
    public const KBIN_SUB_CHANNEL_MAGAZINES = 'showSubscribedMagazines';
    public const KBIN_SUB_CHANNEL_DOMAINS = 'showSubscribedDomains';

    public const TRUE = 'true';
    public const FALSE = 'false';

    public const KEYS = [
        self::KBIN_SUB_CHANNEL_USERS,
        self::KBIN_SUB_CHANNEL_MAGAZINES,
        self::KBIN_SUB_CHANNEL_DOMAINS,
    ];

    public const VALUES = [
        'true',
        'false',
    ];

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(string $key, string $value, Request $request): Response
    {
        $user = $this->getUser();

        $response = new Response();

        if (\in_array($key, self::KEYS) && \in_array($value, self::VALUES)) {
            $user->$key = \in_array($value, [self::TRUE, self::FALSE]) ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : $value;

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

        return new \Symfony\Component\HttpFoundation\RedirectResponse(
            ($request->headers->get('referer') ?? '/').'#settings',
            302,
            $response->headers->all()
        );
    }
}
