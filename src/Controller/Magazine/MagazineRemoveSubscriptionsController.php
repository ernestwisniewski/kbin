<?php

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Service\MagazineManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineRemoveSubscriptionsController extends AbstractController
{
    public function __construct(private readonly MagazineManager $manager)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_remove_subscriptions', $request->request->get('token'));

        $this->manager->removeSubscriptions($magazine);

        return $this->redirectToRefererOrHome($request);
    }
}
