<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PostDto
{
    public Magazine|MagazineDto|null $magazine = null;
    public User|UserDto|null $user = null;
    public Image|ImageDto|null $image = null;
    public ?string $imageUrl = null;
    public ?string $imageAlt = null;
    #[Assert\Length(max: 5000)]
    public ?string $body = null;
    public ?string $lang = 'en';
    public bool $isAdult = false;
    public ?string $slug = null;
    public int $comments = 0;
    public int $uv = 0;
    public int $dv = 0;
    public int $score = 0;
    public ?string $visibility = VisibilityInterface::VISIBILITY_VISIBLE;
    public ?string $ip = null;
    public ?string $apId = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTime $lastActive = null;
    public ?Collection $bestComments = null;
    private ?int $id = null;

    #[Assert\Callback]
    public function validate(
        ExecutionContextInterface $context,
        $payload
    ) {
        $image = Request::createFromGlobals()->files->filter('post');

        if (is_array($image) && isset($image['image'])) {
            $image = $image['image'];
        } else {
            $image = $context->getValue()->image;
        }

        if (empty($this->body) && empty($image)) {
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

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
