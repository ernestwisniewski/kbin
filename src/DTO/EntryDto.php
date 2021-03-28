<?php declare(strict_types=1);

namespace App\DTO;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Magazine;
use App\Entity\Image;
use App\Entity\Entry;

class EntryDto
{
    private ?int $id = null;

    /**
     * @Assert\NotBlank()
     */
    private ?Magazine $magazine = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 2,
     *     max = 255
     * )
     */
    private string $title;

    /**
     * @Assert\Url
     */
    private ?string $url = null;

    /**
     * @Assert\Length(
     *     min = 3,
     *     max = 15000
     * )
     */
    private ?string $body = null;

    private ?Image $image = null;

    private ?bool $isAdult = false;

    private ?Collection $badges = null;

    public function create(
        Magazine $magazine,
        string $title,
        ?string $url = null,
        ?string $body = null,
        ?Image $image = null,
        ?bool $isAdult = false,
        ?Collection $badges = null,
        ?int $id = null
    ): self {
        $this->id       = $id;
        $this->magazine = $magazine;
        $this->title    = $title;
        $this->url      = $url;
        $this->body     = $body;
        $this->image    = $image;
        $this->isAdult  = $isAdult;
        $this->badges   = $badges;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (null === $this->getBody() && null === $this->getUrl()) {
            $this->buildViolation($context, 'url');
            $this->buildViolation($context, 'body');
        }
    }

    private function buildViolation(ExecutionContextInterface $context, $path)
    {
        $context->buildViolation('This value should not be blank.')
            ->atPath($path)
            ->addViolation();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        if ($this->getBody()) {
            return Entry::ENTRY_TYPE_ARTICLE;
        }

        return Entry::ENTRY_TYPE_LINK;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(Magazine $magazine): void
    {
        $this->magazine = $magazine;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): void
    {
        $this->image = $image;
    }

    public function isAdult(): bool
    {
        return $this->isAdult;
    }

    public function setIsAdult(bool $isAdult): self
    {
        $this->isAdult = $isAdult;

        return $this;
    }

    public function getBadges(): ?Collection
    {
        return $this->badges;
    }

    public function setBadges(?Collection $badges): EntryDto
    {
        $this->badges = $badges;

        return $this;
    }
}
