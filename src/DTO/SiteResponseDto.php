<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Site;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class SiteResponseDto implements \JsonSerializable
{
    public const PAGES = [
        'about',
        'contact',
        'faq',
        'privacyPolicy',
        'terms',
    ];

    public ?string $about = null;
    public ?string $contact = null;
    public ?string $faq = null;
    public ?string $privacyPolicy = null;
    public ?string $terms = null;

    public function __construct(?Site $site)
    {
        $this->terms = $site?->terms;
        $this->privacyPolicy = $site?->privacyPolicy;
        $this->faq = $site?->faq;
        $this->about = $site?->about;
        $this->contact = $site?->contact;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'about' => $this->about,
            'contact' => $this->contact,
            'faq' => $this->faq,
            'privacyPolicy' => $this->privacyPolicy,
            'terms' => $this->terms,
        ];
    }
}
