<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Controller\User\ThemeSettingsController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class TwigGlobalSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'registerTwigGlobalUserSettings',
        ];
    }

    public function registerTwigGlobalUserSettings(RequestEvent $request)
    {
        // determine the comment reply position, factoring in the infinite scroll setting (comment reply always on top when infinite scroll enabled)
        $infiniteScroll = $request->getRequest()->cookies->get(ThemeSettingsController::KBIN_GENERAL_INFINITE_SCROLL, ThemeSettingsController::FALSE);
        $commentReplyPosition = $request->getRequest()->cookies->get(ThemeSettingsController::KBIN_COMMENTS_REPLY_POSITION, ThemeSettingsController::TOP);
        if (ThemeSettingsController::TRUE === $infiniteScroll) {
            $commentReplyPosition = ThemeSettingsController::TOP;
        }

        $userSettings = [
            'comment_reply_position' => $commentReplyPosition,
        ];

        $this->twig->addGlobal('user_settings', $userSettings);
    }
}
