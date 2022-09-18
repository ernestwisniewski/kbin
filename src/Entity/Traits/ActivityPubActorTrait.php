<?php declare(strict_types=1);

namespace App\Entity\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait ActivityPubActorTrait
{
    /**
     * @ORM\Column(type="string", nullable=true, options={"default": null})
     */
    public ?string $apId = null;
    /**
     * @ORM\Column(type="string", nullable=true, options={"default": null})
     */
    public ?string $apProfileId = null;
    /**
     * @ORM\Column(type="string", nullable=true, options={"default": null})
     */
    public ?string $apPublicUrl = null;
    /**
     * @ORM\Column(type="string", nullable=true, options={"default": null})
     */
    public ?string $apFollowersUrl = null;
    /**
     * @ORM\Column(type="string", nullable=true, options={"default": null})
     */
    public ?string $apPreferredUsername = null;
    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": null})
     */
    public ?bool $apDiscoverable = null;
    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": null})
     */
    public ?bool $apManuallyApprovesFollowers = null;
    /**
     * @ORM\Column(type="string", nullable=true, options={"default": null})
     */
    public ?string $privateKey = null;
    /**
     * @ORM\Column(type="text", nullable=true, options={"default": null})
     */
    public ?string $publicKey = null;
    /**
     * @ORM\Column(type="datetimetz", nullable=true, options={"default": null})
     */
    public ?DateTime $apFetchedAt = null;

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }
}
