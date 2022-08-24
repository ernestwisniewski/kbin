<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPubManager;
use App\Service\MentionManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MentionsWrapper
{
    public function __construct(
        private ActivityPubManager $manager,
        private UrlGeneratorInterface $urlGenerator,
        private MentionManager $mentionManager,
        private ApHttpClient $client
    ) {
    }

    public function build(?array $mentions, ?string $body = null): array
    {
        $mentions = array_unique(array_merge($mentions ?? [], $this->mentionManager->extract($body ?? '') ?? []));

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
