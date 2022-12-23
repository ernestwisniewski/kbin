<?php declare(strict_types=1);

namespace App\Entity\Traits;

use DateTime;
use Doctrine\ORM\Mapping\Column;

trait ActivityPubActorTrait
{
    #[Column(type: 'string', unique: true, nullable: true)]
    public ?string $apId = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $apProfileId = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $apPublicUrl = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $apFollowersUrl = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $apPreferredUsername = null;

    #[Column(type: 'boolean', nullable: true)]
    public ?bool $apDiscoverable = null;

    #[Column(type: 'boolean', nullable: true)]
    public ?bool $apManuallyApprovesFollowers = null;

    #[Column(type: 'text', nullable: true)]
    public ?string $privateKey = null;

    #[Column(type: 'text', nullable: true)]
    public ?string $publicKey = null;

    #[Column(type: 'datetimetz', nullable: true)]
    public ?DateTime $apFetchedAt = null;

    #[Column(type: 'datetimetz', nullable: true)]
    public ?DateTime $apDeletedAt = null;

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }
}
