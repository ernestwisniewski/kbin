<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Entity\User;
use App\Markdown\CommonMark\Node\MentionLink;
use App\Message\ActivityPub\CreateActorMessage;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\SettingsManager;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MentionLinkParser implements InlineParserInterface
{
    public function __construct(
        private readonly MagazineRepository $magazineRepository,
        private readonly MessageBusInterface $bus,
        private readonly SettingsManager $settingsManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        // support for unicode international domains
        return InlineParserMatch::regex('\B@(\w{1,30})(?:@)?((?:[\pL\pN\pS\pM\-\_]++\.)+[\pL\pN\pM]++|[a-z0-9\-\_]++)?');
    }

    public function parse(InlineParserContext $ctx): bool
    {
        $cursor = $ctx->getCursor();
        $cursor->advanceBy($ctx->getFullMatchLength());

        $matches  = $ctx->getSubMatches();
        $username = $matches['0'];
        $domain   = $matches['1'] ?? $this->settingsManager->get('KBIN_DOMAIN');

        $fullUsername = $username . '@' . $domain;

        [$type, $data] = $this->resolveType($username, $domain);

        if ($data instanceof User && $data->apPublicUrl) {
            $ctx->getContainer()->appendChild(
                $this->generateLink(
                    $data->apPublicUrl, 
                    '@' . $username, 
                    $data->getUsername(), 
                    $data->getUsername(), 
                    MentionType::RemoteUser
                )
            );
            return true;
        }
        
        [$url, $value, $title, $kbinUsername] = match ($type) {
            MentionType::RemoteUser => [$this->resolveUrl(MentionType::User, '@' . $fullUsername), '@' . $username, '@' . $fullUsername, '@' . $fullUsername],
            MentionType::RemoteMagazine => [$this->resolveUrl(MentionType::RemoteMagazine, $fullUsername), '@' . $username, '@' . $fullUsername, $fullUsername],
            MentionType::Magazine => [$this->resolveUrl(MentionType::Magazine, $username), '@' . $username, '@' . $fullUsername, $username],
            MentionType::User => [$this->resolveUrl(MentionType::User, $username), '@' . $username, '@' . $fullUsername, $username],
        };
        
        $ctx->getContainer()->appendChild($this->generateLink($url, $value, $title, $kbinUsername, $type));
        return true;
    }

    private function generateLink(string $url, string $value, string $title, string $kbinUsername, MentionType $type): MentionLink 
    {
        return new MentionLink($url, $value, $title, $kbinUsername, $type);
    }

    private function isRemoteMention(?string $domain): bool
    {
        return $domain !== $this->settingsManager->get('KBIN_DOMAIN');
    }
    
    /**
     * @return array{type: MentionType, data: ?User}
     */
    private function resolveType(string $username, ?string $domain): array
    {
        $isRemote = $this->isRemoteMention($domain);

        if ($isRemote) {
            $user = $this->userRepository->findOneByUsername($username . '@' . $domain);
            // we're aware of this account, link to it directly
            if ($user && $user->apPublicUrl) {
                return [MentionType::RemoteUser, $user];
            }

            // we're not aware, queue it up so we are
            $this->bus->dispatch(new CreateActorMessage($username . '@' . $domain));
        }

        if (
            !isset($user) 
                && $this->magazineRepository->findOneByName(
                    $isRemote 
                        ? $username . '@' . $domain 
                        : $username
            )
        ) {
            return [$isRemote ? MentionType::RemoteMagazine : MentionType::Magazine, null];
        }

        return [$isRemote ? MentionType::RemoteUser : MentionType::User, null];
    }

    private function resolveUrl(MentionType $type, string $slug): string 
    {
        [$route, $param] = match($type) {
            MentionType::Magazine => ['front_magazine', 'name'],
            MentionType::RemoteMagazine => ['front_magazine', 'name'],
            MentionType::RemoteUser => ['user_overview', 'username'],
            MentionType::User => ['user_overview', 'username'],
        };

        return $this->urlGenerator->generate($route, [$param => $slug], UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}
