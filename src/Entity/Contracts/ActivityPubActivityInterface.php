<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Contracts;

interface ActivityPubActivityInterface
{
    public const FOLLOWERS = 'followers';
    public const FOLLOWING = 'following';
    public const INBOX = 'inbox';
    public const OUTBOX = 'outbox';
    public const CONTEXT = 'context';
    public const CONTEXT_URL = 'https://www.w3.org/ns/activitystreams';
    public const SECURITY_URL = 'https://w3id.org/security/v1';
    public const PUBLIC_URL = 'https://www.w3.org/ns/activitystreams#Public';
}
