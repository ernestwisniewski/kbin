<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\ActivityPub;

use App\ActivityPub\JsonRdLink;
use App\Event\ActivityPub\WebfingerResponseEvent;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Webfinger\WebFingerParameters;
use App\Service\ImageManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserWebFingerProfileSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly WebFingerParameters $webfingerParameters,
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ImageManager $imageManager
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
                'ap_user',
                ['username' => $actor->getUserIdentifier()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $link = new JsonRdLink();
            $link->setRel('self')
                ->setType('application/activity+json')
                ->setHref($accountHref);
            $jsonRd->addLink($link);

            if ($actor->avatar) {
                $link = new JsonRdLink();
                $link->setRel('http://webfinger.net/rel/avatar')
                    ->setHref(
                        $this->imageManager->getUrl($actor->avatar),
                    ); // @todo media url
                $jsonRd->addLink($link);
            }
        }
    }

    protected function getActor($name): ?UserInterface
    {
        return $this->userRepository->findOneByUsername($name);
    }
}
