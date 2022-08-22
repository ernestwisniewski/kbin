<?php declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Service\ActivityPubManager;
use App\Utils\RegPatterns;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserLinkParser extends AbstractLocalLinkParser
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private ActivityPubManager $activityPubManager)
    {
    }

    public function getPrefix(): string
    {
        return '@';
    }

    public function getUrl(string $suffix): string
    {
        if (substr_count($suffix, '@') > 1) {
            try {
                return $this->activityPubManager->webfinger($suffix)->getProfileId();
            } catch (\Exception $e) {
            }
        }

        return $this->urlGenerator->generate(
            'user',
            [
                'username' => $suffix,
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
}
