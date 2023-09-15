<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contracts\ContentVisibilityInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PostCommentDto implements ContentVisibilityInterface
{
    public Magazine|MagazineDto|null $magazine = null;
    public User|UserDto|null $user = null;
    public Post|PostDto|null $post = null;
    public ?PostComment $parent = null;
    public ?PostComment $root = null;
    public ?ImageDto $image = null;
    public ?string $imageUrl = null;
    public ?string $imageAlt = null;
    #[Assert\Length(max: 5000)]
    public ?string $body = null;
    public ?string $lang = null;
    public bool $isAdult = false;
    public int $uv = 0;
    public int $favourites = 0;
    public ?bool $isFavourited = null;
    public ?int $userVote = null;
    public ?string $visibility = VisibilityInterface::VISIBILITY_VISIBLE;
    public ?string $ip = null;
    public ?string $apId = null;
    public ?array $mentions = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $editedAt = null;
    public ?\DateTime $lastActive = null;
    private ?int $id = null;

    #[Assert\Callback]
    public function validate(
        ExecutionContextInterface $context,
        $payload
    ) {
        if (empty($this->image)) {
            $image = Request::createFromGlobals()->files->filter('post_comment');

            if (\is_array($image) && isset($image['image'])) {
                $image = $image['image'];
            } else {
                $image = $context->getValue()->image;
            }
        } else {
            $image = $this->image;
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

    public function createWithParent(Post $post, ?PostComment $parent, ImageDto $image = null, string $body = null): self
    {
        $this->post = $post;
        $this->parent = $parent;
        $this->body = $body;
        $this->image = $image;

        if ($parent) {
            $this->root = $parent->root ?? $parent;
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function isPrivate(): bool
    {
        return VisibilityInterface::VISIBILITY_PRIVATE === $this->visibility;
    }

    public function isSoftDeleted(): bool
    {
        return VisibilityInterface::VISIBILITY_SOFT_DELETED === $this->visibility;
    }

    public function isTrashed(): bool
    {
        return VisibilityInterface::VISIBILITY_TRASHED === $this->visibility;
    }

    public function isVisible(): bool
    {
        return VisibilityInterface::VISIBILITY_VISIBLE === $this->visibility;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
