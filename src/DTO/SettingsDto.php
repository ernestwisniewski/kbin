<?php

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class SettingsDto implements \JsonSerializable
{
    public function __construct(
        public string $KBIN_DOMAIN,
        public string $KBIN_TITLE,
        public string $KBIN_META_TITLE,
        public string $KBIN_META_KEYWORDS,
        public string $KBIN_META_DESCRIPTION,
        public string $KBIN_DEFAULT_LANG,
        public string $KBIN_CONTACT_EMAIL,
        public string $KBIN_SENDER_EMAIL,
        public bool $KBIN_JS_ENABLED,
        public bool $KBIN_FEDERATION_ENABLED,
        public bool $KBIN_REGISTRATIONS_ENABLED,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        public array $KBIN_BANNED_INSTANCES,
        public bool $KBIN_HEADER_LOGO,
        public bool $KBIN_CAPTCHA_ENABLED,
        public bool $KBIN_MERCURE_ENABLED,
        public bool $KBIN_ADMIN_ONLY_OAUTH_CLIENTS
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'KBIN_DOMAIN' => $this->KBIN_DOMAIN,
            'KBIN_TITLE' => $this->KBIN_TITLE,
            'KBIN_META_TITLE' => $this->KBIN_META_TITLE,
            'KBIN_META_KEYWORDS' => $this->KBIN_META_KEYWORDS,
            'KBIN_META_DESCRIPTION' => $this->KBIN_META_DESCRIPTION,
            'KBIN_DEFAULT_LANG' => $this->KBIN_DEFAULT_LANG,
            'KBIN_CONTACT_EMAIL' => $this->KBIN_CONTACT_EMAIL,
            'KBIN_SENDER_EMAIL' => $this->KBIN_SENDER_EMAIL,
            'KBIN_JS_ENABLED' => $this->KBIN_JS_ENABLED,
            'KBIN_FEDERATION_ENABLED' => $this->KBIN_FEDERATION_ENABLED,
            'KBIN_REGISTRATIONS_ENABLED' => $this->KBIN_REGISTRATIONS_ENABLED,
            'KBIN_BANNED_INSTANCES' => $this->KBIN_BANNED_INSTANCES,
            'KBIN_HEADER_LOGO' => $this->KBIN_HEADER_LOGO,
            'KBIN_CAPTCHA_ENABLED' => $this->KBIN_CAPTCHA_ENABLED,
            'KBIN_MERCURE_ENABLED' => $this->KBIN_MERCURE_ENABLED,
            'KBIN_ADMIN_ONLY_OAUTH_CLIENTS' => $this->KBIN_ADMIN_ONLY_OAUTH_CLIENTS,
        ];
    }
}
