<?php declare(strict_types=1);

namespace App\Service;

use App\ActivityPub\Server;
use App\Entity\Contracts\ActivityPubActorInterface;
use phpseclib3\Crypt\RSA;

class ActivityPubManager
{
    public function __construct(private Server $server, private SettingsManager $settings)
    {

    }

    public function getActivityPubProfileId(ActivityPubActorInterface $actor): string
    {
        $subject = $actor->getActivityPubId();

        if (!str_contains($subject, '@')) {
            $subject .= '@'.$this->settings->getDto()->KBIN_DOMAIN;
        }

        return $this->server->create()->actor($subject)->webfinger()->getProfileId();
    }

    public function generateKeys(ActivityPubActorInterface $actor): ActivityPubActorInterface
    {
        $private_key = RSA::createKey(4096);

        $actor->publicKey  = (string) $private_key->getPublicKey();
        $actor->privateKey = (string) $private_key;

        return $actor;
    }
}
