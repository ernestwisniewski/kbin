<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Message\ActivityPub\CreateActorMessage;
use App\Repository\UserRepository;
use App\Service\SettingsManager;
use App\Utils\RegPatterns;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserLinkParser extends AbstractLocalLinkParser
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SettingsManager $settingsManager,
        private readonly UserRepository $repository,
        private readonly MessageBusInterface $bus
    ) {
    }

    public function getPrefix(): string
    {
        return '@';
    }

    public function getUrl(string $suffix): string
    {
        $handle = $this->getName($suffix);
        $username = ltrim($handle, '@');

        if (2 == substr_count($handle, '@')) {
            $user = $this->repository->findOneByUsername($suffix);
            if ($user && $user->apPublicUrl) {
                return $user->apPublicUrl;
            }

            $this->bus->dispatch(new CreateActorMessage($suffix));

            $username = $handle;
        }

        return $this->urlGenerator->generate(
            'user_overview',
            [
                'username' => $username,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    protected function getName(string $suffix): string
    {
        $domain = '@'.$this->settingsManager->get('KBIN_DOMAIN');

        $handle = preg_replace('/'.preg_quote($domain, '/').'$/', '', $suffix);

        return trim($handle);
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
