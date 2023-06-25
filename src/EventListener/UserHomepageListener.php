<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class UserHomepageListener
{
    public function __construct(private Security $security, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || $event->getRequest()->getPathInfo() !== '/') {
            return;
        }

        $user = $this->security->getUser();

        if ($user instanceof User && $user->homepage !== User::HOMEPAGE_ALL) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate($user->homepage)));
        }
    }
}
