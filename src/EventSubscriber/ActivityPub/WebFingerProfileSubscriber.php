<?php declare(strict_types=1);

namespace App\EventSubscriber\ActivityPub;

use App\ActivityPub\JsonRdLink;
use App\Event\ActivityPub\WebfingerResponseEvent;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Webfinger\WebFingerParameters;
use App\Service\SettingsManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class WebFingerProfileSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private WebFingerParameters $webfingerParameters,
        private UserRepository $userRepository,
        private UrlGeneratorInterface $urlGenerator,
        private SettingsManager $settings
    ) {
    }

    #[ArrayShape([WebfingerResponseEvent::class => "string"])] public static function getSubscribedEvents(): array
    {
        return [
            WebfingerResponseEvent::class => ['buildResponse', 10],
        ];
    }

    public function buildResponse(WebfingerResponseEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $params  = $this->webfingerParameters->getParams();
        $jsonRd  = $event->jsonRd;

        if (isset($params[WebFingerParameters::ACCOUNT_KEY_NAME]) && $actor = $this->getActor($params[WebFingerParameters::ACCOUNT_KEY_NAME])) {
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
                    ->setHref('https://'.$this->settings->get('KBIN_DOMAIN').'/media/'.$actor->avatar->filePath); // @todo media url
                $jsonRd->addLink($link);
            }
        }
    }

    protected function getActor($name): ?UserInterface
    {
        return $this->userRepository->findOneByUsername($name);
    }
}
