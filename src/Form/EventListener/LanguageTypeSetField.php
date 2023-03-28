<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Form\Type\LanguageType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class LanguageTypeSetField implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        $form->add('lang', LanguageType::class);
    }
}
