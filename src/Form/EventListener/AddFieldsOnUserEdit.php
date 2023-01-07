<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Service\ImageManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Image as ImageConstraint;

final class AddFieldsOnUserEdit implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event): void
    {
        $user = $event->getData();
        $form = $event->getForm();

        if (!$user || null === $user->getId()) {
            return;
        }

        $form->add(
            'avatar',
            FileType::class,
            [
                'constraints' => $this->getConstraint(),
                'mapped' => false,
            ]
        );

        $form->add(
            'cover',
            FileType::class,
            [
                'constraints' => $this->getConstraint('10M'),
                'mapped' => false,
            ]
        );
    }

    private function getConstraint(string $maxSize = '2M'): ImageConstraint
    {
        return new ImageConstraint(
            [
                'detectCorrupted' => true,
                'groups' => ['upload'],
                'maxSize' => $maxSize,
                'mimeTypes' => ImageManager::IMAGE_MIMETYPES,
            ]
        );
    }
}
