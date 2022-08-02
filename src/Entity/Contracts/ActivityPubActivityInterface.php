<?php

namespace App\Entity\Contracts;

interface ActivityPubActivityInterface
{
    const FOLLOWERS = 'followers';
    const FOLLOWING = 'following';
    const INBOX = 'inbox';
    const OUTBOX = 'outbox';
    const CONTEXT = 'context';
    const CONTEXT_URL = 'https://www.w3.org/ns/activitystreams';
    const SECURITY_URL = 'https://w3id.org/security/v1';
    const PUBLIC_URL = 'https://www.w3.org/ns/activitystreams#Public';

}
