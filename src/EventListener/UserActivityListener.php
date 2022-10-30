<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Security;

class UserActivityListener
{
    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[NoReturn] public function onKernelController(ControllerEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MAIN_REQUEST) {
            return;
        }

        if ($this->security->getToken()) {
            $user = $this->security->getToken()->getUser();

            if (($user instanceof User) && !$user->isActiveNow()) {
                $user->lastActive = new \DateTime();
                $this->entityManager->flush();
            }
        }
    }
}
