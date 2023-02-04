<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Magazine;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MagazineVisibilityListener
{
    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        $magazine = array_filter($event->getArguments(), fn ($argument) => $argument instanceof Magazine);

        if (!$magazine) {
            return;
        }

        if (VisibilityInterface::VISIBILITY_VISIBLE !== array_values($magazine)[0]->visibility) {
            throw new NotFoundHttpException();
        }
    }
}
