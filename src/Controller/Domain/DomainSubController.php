<?php declare(strict_types = 1);

namespace App\Controller\Domain;

use App\Controller\AbstractController;
use App\Entity\Domain;
use App\Entity\Magazine;
use App\Service\DomainManager;
use App\Service\MagazineManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DomainSubController extends AbstractController
{
    public function __construct(
        private DomainManager $manager,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function subscribe(Domain $domain, Request $request): Response
    {
        $this->validateCsrf('subscribe', $request->request->get('token'));

        $this->manager->subscribe($domain, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $domain->subscriptionsCount,
                    'isSubscribed' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function unsubscribe(Domain $domain, Request $request): Response
    {
        $this->validateCsrf('subscribe', $request->request->get('token'));

        $this->manager->unsubscribe($domain, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $domain->subscriptionsCount,
                    'isSubscribed' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}
