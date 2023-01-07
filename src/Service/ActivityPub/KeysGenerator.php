<?php

declare(strict_types=1);

namespace App\Service\ActivityPub;

use App\Entity\Contracts\ActivityPubActorInterface;
use phpseclib3\Crypt\RSA;

class KeysGenerator
{
    public static function generate(ActivityPubActorInterface $actor): ActivityPubActorInterface
    {
        $privateKey = RSA::createKey(4096);

        $actor->publicKey = (string) $privateKey->getPublicKey();
        $actor->privateKey = (string) $privateKey;

        return $actor;
    }
}
