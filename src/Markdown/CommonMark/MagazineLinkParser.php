<?php

namespace App\Markdown\CommonMark;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class MagazineLinkParser extends AbstractLocalLinkParser {
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator) {
        $this->urlGenerator = $urlGenerator;
    }

    public function getPrefix(): string {
        return 'm';
    }

    public function getRegex(): string {
        return '/^(?:\w{3,25}\+){0,70}\w{3,25}\b/';
    }

    public function getUrl(string $suffix): string {
        return $this->urlGenerator->generate('magazine_front', [
            'name' => $suffix,
        ]);
    }
}
