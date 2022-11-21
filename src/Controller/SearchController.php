<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Magazine;
use App\Entity\User;
use App\Message\ActivityPub\Inbox\ActivityMessage;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPubManager;
use App\Service\SearchManager;
use App\Utils\RegPatterns;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class SearchController extends AbstractController
{
    public function __construct(
        private SearchManager $manager,
        private ActivityPubManager $activityPubManager,
        private MessageBusInterface $bus,
        private ApHttpClient $apHttpClient
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $query = $request->query->get('q');

        $object = null;
        $type = null;
        if (str_contains($query, '@')) {
            $name = str_starts_with($query, '@') ? $query : '@'.$query;
            preg_match(RegPatterns::AP_USER, $name, $matches);
            if (count(array_filter($matches)) >= 4) {
                try {
                    $webfinger = $this->activityPubManager->webfinger($name);
                    $object = $this->activityPubManager->findActorOrCreate($webfinger->getProfileId());
                    if ($object instanceof Magazine) { // @todo
                        $type = 'magazine';
                    } elseif ($object instanceof User) {
                        $type = 'user';
                    }
                } catch (\Exception $e) {
                }
            }
        }

        if (false !== filter_var($query, FILTER_VALIDATE_URL)) {
            $body = $this->apHttpClient->getActivityObject($query, false);

            $this->bus->dispatch(new ActivityMessage($body));
        }

        return $this->render(
            'search/front.html.twig',
            [
                'type' => $type,
                'object' => $object,
                'results' => $this->manager->findPaginated($query, $this->getPageNb($request)),
                'q' => $request->query->get('q'),
            ]
        );
    }
}
