<?php declare(strict_types = 1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Service\MagazineManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineSubController extends AbstractController
{
    public function __construct(
        private MagazineManager $manager,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("subscribe", subject="magazine")
     */
    public function subscribe(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('subscribe', $request->request->get('token'));

        $this->manager->subscribe($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $magazine->subscriptionsCount,
                    'isSubscribed' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("subscribe", subject="magazine")
     */
    public function unsubscribe(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('subscribe', $request->request->get('token'));

        $this->manager->unsubscribe($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $magazine->subscriptionsCount,
                    'isSubscribed' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}
