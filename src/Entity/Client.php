<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use League\Bundle\OAuth2ServerBundle\Model\AbstractClient;
use League\OAuth2\Server\Entities\ClientEntityInterface;

#[Entity]
#[Table(name: '`oauth2_client`')]
class Client extends AbstractClient implements ClientEntityInterface
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue(strategy: 'NONE')]
    #[Column(type: 'string', length: 32, unique: true)]
    protected $identifier;

    #[Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[OneToOne(targetEntity: User::class)]
    #[JoinColumn]
    private ?User $user = null;

    #[OneToOne(targetEntity: Image::class, cascade: ['persist'])]
    #[JoinColumn]
    private ?Image $image = null;

    #[Column(type: 'string')]
    private ?string $contactEmail = null;

    #[OneToMany(mappedBy: 'client', targetEntity: OAuth2UserConsent::class, orphanRemoval: true)]
    private ?Collection $oAuth2UserConsents = null;

    #[OneToMany(mappedBy: 'client', targetEntity: OAuth2ClientAccess::class, orphanRemoval: true)]
    private Collection $oAuth2ClientAccesses;

    public function __construct(string $name, string $identifier, ?string $secret)
    {
        parent::__construct($name, $identifier, $secret);
        $this->oAuth2UserConsents = new ArrayCollection();
        $this->oAuth2ClientAccesses = new ArrayCollection();
        $this->createdAtTraitConstruct();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): self
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, OAuth2UserConsent>
     */
    public function getOAuth2UserConsents(): Collection
    {
        return $this->oAuth2UserConsents;
    }

    public function addOAuth2UserConsent(OAuth2UserConsent $oAuth2UserConsent): self
    {
        if (!$this->oAuth2UserConsents->contains($oAuth2UserConsent)) {
            $this->oAuth2UserConsents->add($oAuth2UserConsent);
            $oAuth2UserConsent->setClient($this);
        }

        return $this;
    }

    public function removeOAuth2UserConsent(OAuth2UserConsent $oAuth2UserConsent): self
    {
        if ($this->oAuth2UserConsents->removeElement($oAuth2UserConsent)) {
            // set the owning side to null (unless already changed)
            if ($oAuth2UserConsent->getClient() === $this) {
                $oAuth2UserConsent->setClient(null);
            }
        }

        return $this;
    }

    public function getRedirectUri()
    {
        return $this->getRedirectUris();
    }

    /**
     * @return Collection<int, OAuth2ClientAccess>
     */
    public function getOAuth2ClientAccesses(): Collection
    {
        return $this->oAuth2ClientAccesses;
    }

    public function addOAuth2ClientAccess(OAuth2ClientAccess $oAuth2ClientAccess): self
    {
        if (!$this->oAuth2ClientAccesses->contains($oAuth2ClientAccess)) {
            $this->oAuth2ClientAccesses->add($oAuth2ClientAccess);
            $oAuth2ClientAccess->setClient($this);
        }

        return $this;
    }

    public function removeOAuth2ClientAccess(OAuth2ClientAccess $oAuth2ClientAccess): self
    {
        if ($this->oAuth2ClientAccesses->removeElement($oAuth2ClientAccess)) {
            // set the owning side to null (unless already changed)
            if ($oAuth2ClientAccess->getClient() === $this) {
                $oAuth2ClientAccess->setClient(null);
            }
        }

        return $this;
    }
}
