<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Entity\Magazine;
use App\Entity\User;
use App\Markdown\CommonMark\Node\MentionLink;
use App\Markdown\CommonMark\Node\UnresolvableLink;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\SettingsManager;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
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
                $this->generateNode(
                    $data->apPublicUrl, 
                    '@' . $username, 
                    '@' . $data->apId, 
                    '@' . $data->apId, 
                    MentionType::RemoteUser,
                )
            );
            return true;
        }

        if ($data instanceof Magazine && $data->apPublicUrl) {
            $ctx->getContainer()->appendChild(
                $this->generateNode(
                    $data->apPublicUrl, 
                    '@' . $username, 
                    '@' . $data->apId, 
                    $data->apId, 
                    MentionType::RemoteMagazine,
                )
            );
            return true;
        }
        
        [$url, $value, $title, $kbinUsername] = match ($type) {
            MentionType::RemoteUser     => [$this->resolveUrl($type, '@' . $fullUsername), '@' . $username, '@' . $fullUsername, '@' . $fullUsername],
            MentionType::RemoteMagazine => [$this->resolveUrl($type, $fullUsername), '@' . $username, '@' . $fullUsername, $fullUsername],
            MentionType::Magazine       => [$this->resolveUrl($type, $username), '@' . $username, '@' . $fullUsername, $username],
            MentionType::Search         => [$this->resolveUrl($type, $fullUsername), '@' . $username, '@' . $fullUsername, $fullUsername],
            MentionType::Unresolvable   => ['', '@' . $username, '@' . $username, $username],
            MentionType::User           => [$this->resolveUrl($type, $username), '@' . $username, '@' . $fullUsername, $username],
        };
        
        $ctx->getContainer()->appendChild($this->generateNode($url, $value, $title, $kbinUsername, $type));
        return true;
    }

    private function generateNode(string $url, string $value, string $title, string $kbinUsername, MentionType $type): Node
    {
        if ($type === MentionType::Unresolvable) {
            return new UnresolvableLink($value);
        }

        return new MentionLink($url, $value, $title, $kbinUsername, $type);
    }

    private function isRemoteMention(?string $domain): bool
    {
        return $domain !== $this->settingsManager->get('KBIN_DOMAIN');
    }
    
    /**
     * @return array{type: MentionType, data: User|Magazine|null}
     */
    private function resolveType(string $handle, ?string $domain): array
    {
        if ($this->isRemoteMention($domain)) {
            return $this->resolveRemoteType($handle . '@' . $domain);
        }

        if ($this->userRepository->findOneByUsername($handle) !== null) {
            return [MentionType::User, null];
        }

        if ($this->magazineRepository->findOneByName($handle) !== null) {
            return [MentionType::Magazine, null];
        }

        return [MentionType::Unresolvable, null];
    }

    /**
     * @return array{type: MentionType, data: User|Magazine|null}
     */
    private function resolveRemoteType($fullyQualifiedHandle): array
    {
        $user = $this->userRepository->findOneByUsername('@' . $fullyQualifiedHandle);
        // we're aware of this account, link to it directly
        if ($user && $user->apPublicUrl) {
            return [MentionType::RemoteUser, $user];
        }

        $magazine = $this->magazineRepository->findOneByName($fullyQualifiedHandle);
        // we're aware of this magazine, link to it directly
        if ($magazine && $magazine->apPublicUrl) {
            return [MentionType::RemoteMagazine, $magazine];
        }

        // take thee to search
        return [MentionType::Search, null];
    }

    private function resolveUrl(MentionType $type, string $slug): string 
    {
        [$route, $param] = match($type) {
            MentionType::Magazine       => ['front_magazine', 'name'],
            MentionType::RemoteMagazine => ['front_magazine', 'name'],
            MentionType::RemoteUser     => ['user_overview', 'username'],
            MentionType::Search         => ['search', 'q'],
            MentionType::User           => ['user_overview', 'username'],
        };

        return $this->urlGenerator->generate($route, [$param => $slug], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
