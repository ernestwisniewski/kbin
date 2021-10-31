<?php declare(strict_types = 1);

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class DisableFieldsOnMagazineEdit implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event): void
    {
        $magazine = $event->getData();
        $form     = $event->getForm();

        if (!$magazine || null === $magazine->getId()) {
            return;
        }

        $field             = $form->get('name');
        $attrs             = $field->getConfig()->getOptions();
        $attrs['disabled'] = 'disabled';

        $form->remove($field->getName());
        $form->add(
            $field->getName(),
            get_class($field->getConfig()->getType()->getInnerType()),
            $attrs
        );
    }
}
