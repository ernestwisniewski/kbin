<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Domain;
use App\Entity\Entry;
use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
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
    public ?string $title = null;
    #[Assert\Url]
    public ?string $url = null;
    #[Assert\Length(min: 2, max: 35000)]
    public ?string $body = null;
    public ?string $lang = null;
    public int $comments = 0;
    public int $uv = 0;
    public int $dv = 0;
    public bool $isOc = false;
    public bool $isAdult = false;
    public ?Collection $badges = null;
    public ?string $slug = null;
    public int $views = 0;
    public int $score = 0;
    public ?string $visibility = null;
    public ?string $ip = null;
    public ?array $tags = null;
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTime $lastActive = null;
    private ?int $id = null;

    #[Assert\Callback]
    public function validate(
        ExecutionContextInterface $context,
        $payload
    ) {
        $image = Request::createFromGlobals()->files->filter('entry_image');
        if (is_array($image)) {
            $image = $image['image'];
        } else {
            $image = $context->getValue()->image;
        }

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

    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function setIsEng(string $lang)
    {
        $this->lang = $lang ? 'en' : null;
    }

    public function isEng(): bool
    {
        return (bool) $this->lang;
    }
}
