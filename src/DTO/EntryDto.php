<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Entry;
use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EntryDto
{
    #[Assert\NotBlank]
    public Magazine|MagazineDto|null $magazine = null;
    public User|UserDto|null $user = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $title;
    #[Assert\Url]
    public ?string $url = null;
    #[Assert\Length(min: 2, max: 15000)]
    public ?string $body = null;
    public ?Image $image = null;
    public ?bool $isAdult = false;
    public ?Collection $badges = null;
    private ?int $id = null;

    public function create(
        Magazine $magazine,
        User $user,
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
        $this->user     = $user;
        $this->title    = $title;
        $this->url      = $url;
        $this->body     = $body;
        $this->image    = $image;
        $this->isAdult  = $isAdult;
        $this->badges   = $badges;

        return $this;
    }

    #[Assert\Callback]
    public function validate(
        ExecutionContextInterface $context,
        $payload
    ) {
        if (null === $this->body && null === $this->url) {
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
        if ($this->body) {
            return Entry::ENTRY_TYPE_ARTICLE;
        }

        return Entry::ENTRY_TYPE_LINK;
    }
}
