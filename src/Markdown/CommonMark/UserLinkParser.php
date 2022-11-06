<?php declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Repository\UserRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use App\Utils\RegPatterns;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserLinkParser extends AbstractLocalLinkParser
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ActivityPubManager $activityPubManager,
        private SettingsManager $settingsManager,
        private ApHttpClient $client,
        private UserRepository $repository
    ) {
    }

    public function getPrefix(): string
    {
        return '@';
    }

    public function getUrl(string $suffix): string
    {
        $handle = $this->getName($suffix);

        if (substr_count($handle, '@') == 2) {
            try {
                $user = $this->repository->findOneByUsername($suffix);
                if ($user && $user->apPublicUrl) {
                    return $user->apPublicUrl;
                }

                $profileId = $this->activityPubManager->webfinger($suffix)->getProfileId();
                $actor = $this->client->getActorObject($profileId);

                if ($profileId || $actor) {
                    return !empty($actor['url']) ? $actor['url'] : $profileId;
                }
            } catch (\Exception $e) {
                return '#';
            }
        }

        return $this->urlGenerator->generate(
            'user',
            [
                'username' => ltrim($handle, '@'),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function getRegex(): string
    {
        return RegPatterns::LOCAL_USER;
    }

    public function getApRegex(): string
    {
        return RegPatterns::AP_USER;
    }

    protected function getName(string $suffix): string
    {
        $domain = '@'.$this->settingsManager->get('KBIN_DOMAIN');

        $handle = preg_replace('/'.preg_quote($domain, '/').'$/', '', $suffix);

        return trim($handle);
    }
}
