<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPubManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MentionsWrapper
{
    public function __construct(
        private ActivityPubManager $manager,
        private UrlGeneratorInterface $urlGenerator,
        private ApHttpClient $client
    ) {
    }

    public function build(?array $mentions): array
    {
        $results = [];

        foreach ($mentions as $index => $mention) {
            try {
                $actor = $this->manager->findActorOrCreate($mention);
            } catch (\Exception $e) {
                continue;
            }

            $results[$index] = [
                'type' => 'Mention',
                'href' => $actor->apProfileId
                    ? $this->client->getActorObject($actor->apProfileId)['url']
                    : $this->urlGenerator->generate(
                        'ap_user',
                        ['username' => $actor->getUserIdentifier()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                'name' => $mention,
            ];
        }

        return $results;
    }
}
