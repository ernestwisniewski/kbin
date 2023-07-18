<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Entity\Magazine;
use App\Entity\User;
use App\Markdown\CommonMark\Node\ActivityPubMentionLink;
use App\Markdown\CommonMark\Node\RoutedMentionLink;
use App\Markdown\CommonMark\Node\UnresolvableLink;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\SettingsManager;
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
                new ActivityPubMentionLink(
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
                new ActivityPubMentionLink(
                    $data->apPublicUrl, 
                    '@' . $username, 
                    '@' . $data->apId, 
                    $data->apId, 
                    MentionType::RemoteMagazine,
                )
            );
            return true;
        }
        
        [$routeDetails, $slug, $label, $title, $kbinUsername] = match ($type) {
            MentionType::RemoteUser     => [$this->resolveRouteDetails($type), '@' . $fullUsername, '@' . $username, '@' . $fullUsername, '@' . $fullUsername],
            MentionType::RemoteMagazine => [$this->resolveRouteDetails($type), $fullUsername, '@' . $username, '@' . $fullUsername, $fullUsername],
            MentionType::Magazine       => [$this->resolveRouteDetails($type), $username, '@' . $username, '@' . $fullUsername, $username],
            MentionType::Search         => [$this->resolveRouteDetails($type), $fullUsername, '@' . $username, '@' . $fullUsername, $fullUsername],
            MentionType::Unresolvable   => [['route' => '', 'param' => ''], '', '@' . $username, '', ''],
            MentionType::User           => [$this->resolveRouteDetails($type), $username, '@' . $username, '@' . $fullUsername, $username],
        };
        
        $ctx->getContainer()->appendChild(
            $this->generateNode(
                ...$routeDetails, 
                slug:         $slug, 
                label:        $label,
                title:        $title, 
                kbinUsername: $kbinUsername, 
                type:         $type,
            )
        );
        return true;
    }

    private function generateNode(string $route, string $param, string $slug, string $label, string $title, string $kbinUsername, MentionType $type): Node
    {
        if ($type === MentionType::Unresolvable) {
            return new UnresolvableLink($label);
        }

        return new RoutedMentionLink($route, $param, $slug, $label, $title, $kbinUsername, $type);
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

    /**
     * @param MentionType $type
     * @return array{route: string, param: string}
     */
    private function resolveRouteDetails(MentionType $type): array 
    {
        return match($type) {
            MentionType::Magazine       => ['route' => 'front_magazine', 'param' => 'name'],
            MentionType::RemoteMagazine => ['route' => 'front_magazine', 'param' => 'name'],
            MentionType::RemoteUser     => ['route' => 'user_overview',  'param' => 'username'],
            MentionType::Search         => ['route' => 'search',         'param' => 'q'],
            MentionType::User           => ['route' => 'user_overview',  'param' => 'username'],
        };
    }
}
