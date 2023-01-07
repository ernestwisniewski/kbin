<?php

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
