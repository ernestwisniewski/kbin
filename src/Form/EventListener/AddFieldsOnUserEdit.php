<?php declare(strict_types = 1);

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Image as ImageConstraint;

final class AddFieldsOnUserEdit implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
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

        $imageConstraint = new ImageConstraint(
            [
                'detectCorrupted' => true,
                'groups'          => ['upload'],
                'maxSize'         => '2M',
                'mimeTypes'       => ['image/jpeg', 'image/gif', 'image/png'],
            ]
        );

        $form->add(
            'avatar',
            FileType::class,
            [
                'constraints' => $imageConstraint,
                'mapped'      => false,
            ]
        );
    }
}
