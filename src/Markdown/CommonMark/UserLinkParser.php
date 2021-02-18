<?php

namespace App\Markdown\CommonMark;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserLinkParser extends AbstractLocalLinkParser {
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator) {
        $this->urlGenerator = $urlGenerator;
    }

    public function getPrefix(): string {
        return 'u';
    }

    public function getUrl(string $suffix): string {
        return $this->urlGenerator->generate('user', [
            'username' => $suffix,
        ]);
    }

    public function getRegex(): string {
        return '/^\w{3,25}\b/';
    }
}
