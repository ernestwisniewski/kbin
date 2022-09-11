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
        if (substr_count($suffix, '@') > 1 && !str_ends_with($suffix, '@'.$this->settingsManager->get('KBIN_DOMAIN'))) {
            try {
                $user = $this->repository->findOneByUsername($suffix);
                if($user) {
                    return $user->apPublicUrl;
                }

                $profileId = $this->activityPubManager->webfinger($suffix)->getProfileId();
                $actor = $this->client->getActorObject($profileId);

                if ($profileId || $actor) {
                    return !empty($actor['url']) ? $actor['url'] : $profileId;
                }
            } catch (\Exception $e) {
            }
        }

        if (str_ends_with($suffix, '@'.$this->settingsManager->get('KBIN_DOMAIN'))) {
            $suffix = rtrim($suffix, '@'.$this->settingsManager->get('KBIN_DOMAIN'));
        }

        return $this->urlGenerator->generate(
            'user',
            [
                'username' => substr_count($suffix, '@') == 2 ? $suffix : ltrim($suffix, '@'),
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
        if (substr_count($suffix, '@') == 2) {
            return '@'.explode('@', $suffix)[1];
        }

        return $suffix;
    }
}
