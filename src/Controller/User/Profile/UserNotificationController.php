<?php declare(strict_types = 1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Repository\NotificationRepository;
use App\Service\NotificationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserNotificationController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function notifications(NotificationRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/profile/notifications.html.twig',
            [
                'notifications' => $repository->findByUser($this->getUserOrThrow(), $this->getPageNb($request)),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function read(NotificationManager $manager, Request $request): Response
    {
        $this->validateCsrf('read_notifications', $request->request->get('token'));

        $manager->markAllAsRead($this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function clear(NotificationManager $manager, Request $request): Response
    {
        $this->validateCsrf('clear_notifications', $request->request->get('token'));

        $manager->clear($this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }
}
