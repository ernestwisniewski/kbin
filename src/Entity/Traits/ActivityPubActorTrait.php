<?php declare(strict_types=1);

namespace App\Entity\Traits;

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
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $privateKey = null;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $publicKey = null;

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }
}
