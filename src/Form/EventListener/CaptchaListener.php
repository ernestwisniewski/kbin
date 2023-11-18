<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Service\SettingsManager;
use MeteoConcept\HCaptchaBundle\Form\HCaptchaType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class CaptchaListener implements EventSubscriberInterface
{
    public function __construct(private readonly SettingsManager $settingsManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event): void
    {
        if (!$this->settingsManager->get('KBIN_CAPTCHA_ENABLED')) {
            return;
        }

        $form = $event->getForm();

        $form->add('captcha', HCaptchaType::class, [
            'label' => 'Captcha',
        ]);
    }
}
