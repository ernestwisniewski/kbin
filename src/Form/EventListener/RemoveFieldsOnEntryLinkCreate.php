<?php declare(strict_types = 1);

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class RemoveFieldsOnEntryLinkCreate implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event): void
    {
        $entry = $event->getData();
        $form  = $event->getForm();

        if ($entry && $entry->getId()) {
            return;
        }

        $form->remove($form->get('image')->getName());
    }
}
