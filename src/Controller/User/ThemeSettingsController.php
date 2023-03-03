<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ThemeSettingsController extends AbstractController
{
    const ENTRIES_VIEW = 'entries_view';
    const ENTRY_COMMENTS_VIEW = 'entry_comments_view';
    const POST_COMMENTS_VIEW = 'post_comments_view';

    const CLASSIC = 'classic';
    const CHAT = 'chat';
    const TREE = 'tree';
    const COMPACT = 'compact';

    const KEYS = [
        self::ENTRIES_VIEW,
        self::ENTRY_COMMENTS_VIEW,
        self::POST_COMMENTS_VIEW,
    ];

    const VALUES = [
        self::CLASSIC,
        self::CHAT,
        self::TREE,
        self::COMPACT,
    ];

    public function __invoke(string $key, string $value, Request $request): Response
    {
        $response = new Response();

        if (in_array($key, self::KEYS) && in_array($value, self::VALUES)) {
            $response->headers->setCookie(new Cookie($key, $value));
        }

        return new \Symfony\Component\HttpFoundation\RedirectResponse(
            $request->headers->get('referer') ?? '/',
            302,
            $response->headers->all()
        );
    }
}
