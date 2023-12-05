<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\ActivityPub;

use App\ActivityPub\JsonRdLink;
use App\Entity\Magazine;
use App\Kbin\ActivityPub\Webfinger\EventSubscriber\Event\WebfingerResponseEvent;
use App\Repository\MagazineRepository;
use App\Service\ActivityPub\Webfinger\WebFingerParameters;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupWebFingerProfileSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly WebFingerParameters $webfingerParameters,
        private readonly MagazineRepository $magazineRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[ArrayShape([WebfingerResponseEvent::class => 'string'])]
    public static function getSubscribedEvents(): array
    {
        return [
            WebfingerResponseEvent::class => ['buildResponse', 999],
        ];
    }

    public function buildResponse(WebfingerResponseEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $params = $this->webfingerParameters->getParams();
        $jsonRd = $event->jsonRd;

        if (isset($params[WebFingerParameters::ACCOUNT_KEY_NAME]) && $actor = $this->getActor(
            $params[WebFingerParameters::ACCOUNT_KEY_NAME]
        )) {
            $accountHref = $this->urlGenerator->generate(
                'ap_magazine',
                ['name' => $actor->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $link = new JsonRdLink();
            $link->setRel('self')
                ->setType('application/activity+json')
                ->setHref($accountHref);
            $jsonRd->addLink($link);
        }
    }

    protected function getActor($name): ?Magazine
    {
        return $this->magazineRepository->findOneByName($name);
    }
}
