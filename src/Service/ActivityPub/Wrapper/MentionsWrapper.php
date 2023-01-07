<?php

declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Service\ActivityPubManager;
use App\Service\MentionManager;
use App\Service\SettingsManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MentionsWrapper
{
    public function __construct(
        private readonly ActivityPubManager $manager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MentionManager $mentionManager,
        private readonly SettingsManager $settingsManager
    ) {
    }

    public function build(?array $mentions, ?string $body = null): array
    {
        $mentions = array_unique(array_merge($mentions ?? [], $this->mentionManager->extract($body ?? '') ?? []));

        $results = [];
        foreach ($mentions as $index => $mention) {
            try {
                $actor = $this->manager->findActorOrCreate($mention);

                if (!$actor) {
                    continue;
                }

                if (substr_count($mention, '@') < 2) {
                    $mention = $mention.'@'.$this->settingsManager->get('KBIN_DOMAIN');
                }

                $results[$index] = [
                    'type' => 'Mention',
                    'href' => $actor->apProfileId ??
                        $this->urlGenerator->generate(
                            'ap_user',
                            ['username' => $actor->getUserIdentifier()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                    'name' => $mention,
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $results;
    }
}
