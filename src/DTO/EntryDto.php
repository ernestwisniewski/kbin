<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Domain;
use App\Entity\Entry;
use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EntryDto
{
    #[Assert\NotBlank]
    public Magazine|MagazineDto|null $magazine = null;
    public User|UserDto|null $user = null;
    public Image|ImageDto|null $image = null;
    public Domain|DomainDto|null $domain = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $title;
    #[Assert\Url]
    public ?string $url = null;
    #[Assert\Length(min: 2, max: 15000)]
    public ?string $body = null;
    public ?int $comments = null;
    public ?int $uv = null;
    public ?int $dv = null;
    public ?bool $isAdult = false;
    public ?Collection $badges = null;
    public ?string $slug = null;
    public ?int $views = null;
    public ?int $score = null;
    public ?string $visibility = null;
    public ?string $ip = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTime $lastActive = null;
    private ?int $id = null;

    public function create(
        Magazine $magazine,
        User $user,
        string $title,
        ?string $url = null,
        ?string $body = null,
        ?int $comments = null,
        ?int $uv = null,
        ?int $dv = null,
        ?Domain $domain = null,
        ?Image $image = null,
        ?bool $isAdult = false,
        ?Collection $badges = null,
        ?string $slug = null,
        ?int $score = null,
        ?int $views = null,
        ?string $visibility = null,
        ?string $ip = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTime $lastActive = null,
        ?int $id = null
    ): self {
        $this->id         = $id;
        $this->magazine   = $magazine;
        $this->domain     = $domain;
        $this->user       = $user;
        $this->title      = $title;
        $this->url        = $url;
        $this->body       = $body;
        $this->comments   = $comments;
        $this->uv         = $uv;
        $this->dv         = $dv;
        $this->image      = $image;
        $this->isAdult    = $isAdult;
        $this->badges     = $badges;
        $this->slug       = $slug;
        $this->score      = $score;
        $this->views      = $views;
        $this->visibility = $visibility;
        $this->ip         = $ip;
        $this->createdAt  = $createdAt;
        $this->lastActive  = $lastActive;

        return $this;
    }

    #[Assert\Callback]
    public function validate(
        ExecutionContextInterface $context,
        $payload
    ) {
        $image = Request::createFromGlobals()->files->filter('entry_image');
        if (is_array($image)) {
            $image = $image['image'];
        }
        $image = empty($image) ? null : $image;
        if (null === $this->body && null === $this->url && null === $image) {
            $this->buildViolation($context, 'url');
            $this->buildViolation($context, 'body');
            $this->buildViolation($context, 'image');
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
        if ($this->url) {
            return Entry::ENTRY_TYPE_LINK;
        }

        $type = Entry::ENTRY_TYPE_IMAGE;

        if ($this->body) {
            $type = Entry::ENTRY_TYPE_ARTICLE;
        }

        return $type;
    }
}
