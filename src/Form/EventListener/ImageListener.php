<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Factory\ImageFactory;
use App\Repository\ImageRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormEvents;

final class ImageListener implements EventSubscriberInterface
{
    private string $fieldName;

    public function __construct(
        private readonly ImageRepository $images,
        private readonly ImageFactory $factory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => ['onPostSubmit', -200],
        ];
    }

    public function onPostSubmit(PostSubmitEvent $event): void
    {
        if (!$event->getForm()->isValid()) {
            return;
        }

        $data = $event->getData();

        $fieldName = $this->fieldName ?? 'image';

        if (!$event->getForm()->has($fieldName)) {
            return;
        }

        $upload = $event->getForm()->get($fieldName)->getData();

        if ($upload) {
            $image = $this->images->findOrCreateFromUpload($upload);
            $data->$fieldName = $this->factory->createDto($image);
        }
    }

    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;

        return $this;
    }
}
