<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;

final class DefaultLanguage implements EventSubscriberInterface
{
    private $locale = '';

    public function __construct(RequestStack $requestStack)
    {
        $this->locale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event): void
    {
        $dto = $event->getData();

        if (null !== $dto && null === $dto->lang) {
            $dto->lang = $event->getForm()->getConfig()->getOption('parentLanguage', $this->locale);
            
            $event->setData($dto);
        }
    }
}
