<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Markdown\CommonMark\Node\ActorSearchLink;
use App\Markdown\CommonMark\Node\CommunityLink;
use App\Markdown\CommonMark\Node\UnresolvableLink;
use App\Repository\MagazineRepository;
use App\Service\SettingsManager;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommunityLinkParser implements InlineParserInterface
{
    public function __construct(
        private readonly MagazineRepository $magazineRepository,
        private readonly SettingsManager $settingsManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex('\B!(\w{1,30})(?:@)?((?:[\pL\pN\pS\pM\-\_]++\.)+[\pL\pN\pM]++|[a-z0-9\-\_]++)?');
    }

    public function parse(InlineParserContext $ctx): bool
    {
        $cursor = $ctx->getCursor();
        $cursor->advanceBy($ctx->getFullMatchLength());

        $matches = $ctx->getSubMatches();
        $handle = $matches['0'];
        $domain = $matches['1'] ?? $this->settingsManager->get('KBIN_DOMAIN');

        $fullHandle = $handle.'@'.$domain;
        $isRemote = $this->isRemoteCommunity($domain);

        if ($isRemote) {
            if ($magazine = $this->magazineRepository->findOneByName($fullHandle)) {
                $ctx->getContainer()->appendChild(
                    new CommunityLink(
                        $magazine->apPublicUrl,
                        '!'.$handle,
                        '!'.$magazine->apId,
                        $magazine->apId,
                        MentionType::RemoteMagazine,
                    ),
                );

                return true;
            }

            $ctx->getContainer()->appendChild(
                new ActorSearchLink(
                    $this->urlGenerator->generate('search', ['q' => $fullHandle], UrlGeneratorInterface::ABSOLUTE_URL),
                    '!'.$handle,
                    '!'.$fullHandle,
                )
            );

            return true;
        }

        // unable to resolve a local '!' link so don't even try.
        $ctx->getContainer()->appendChild(new UnresolvableLink('!'.$handle));

        return true;
    }

    private function isRemoteCommunity(?string $domain): bool
    {
        return $domain !== $this->settingsManager->get('KBIN_DOMAIN');
    }
}
