<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\DTO;

use App\DTO\Contracts\UserDtoInterface;
use App\DTO\ImageDto;
use App\Entity\User;
use App\Utils\RegPatterns;
use App\Validator\Unique;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Unique(User::class, errorPath: 'email', fields: ['email'], idFields: ['id'])]
#[Unique(User::class, errorPath: 'username', fields: ['username'], idFields: ['id'])]
class UserDto implements UserDtoInterface
{
    public const MAX_USERNAME_LENGTH = 30;
    public const MAX_ABOUT_LENGTH = 512;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: self::MAX_USERNAME_LENGTH)]
    #[Assert\Regex(pattern: RegPatterns::USERNAME, match: true)]
    public ?string $username = null;
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;
    #[Assert\Length(min: 6, max: 4096)]
    public ?string $plainPassword = null; // @todo move password and agreeTerms to RegisterDto
    #[Assert\Length(min: 2, max: self::MAX_ABOUT_LENGTH)]
    public ?string $about = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?string $fields = null;
    public ?ImageDto $avatar = null;
    public ?ImageDto $cover = null;
    public bool $agreeTerms = false;
    public ?string $ip = null;
    public ?string $apId = null;
    public ?string $apProfileId = null;
    public ?int $id = null;
    public ?int $followersCount = 0;
    public ?bool $isBot = null;
    public ?bool $isFollowedByUser = null;
    public ?bool $isFollowerOfUser = null;
    public ?bool $isBlockedByUser = null;
    public ?string $totpSecret = null;

    #[Assert\Callback]
    public function validate(
        ExecutionContextInterface $context,
        $payload
    ) {
        if (!Request::createFromGlobals()->request->has('user_register')) {
            return;
        }

        if (false === $this->agreeTerms) {
            $this->buildViolation($context, 'agreeTerms');
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

    public static function create(
        string $username,
        string $email = null,
        ImageDto $avatar = null,
        ImageDto $cover = null,
        string $about = null,
        \DateTimeImmutable $createdAt = null,
        array $fields = null,
        string $apId = null,
        string $apProfileId = null,
        int $id = null,
        ?int $followersCount = 0,
        bool $isBot = null,
    ): self {
        $dto = new UserDto();
        $dto->id = $id;
        $dto->username = $username;
        $dto->email = $email;
        $dto->avatar = $avatar;
        $dto->cover = $cover;
        $dto->about = $about;
        $dto->createdAt = $createdAt;
        $dto->fields = $fields;
        $dto->apId = $apId;
        $dto->apProfileId = $apProfileId;
        $dto->followersCount = $followersCount;
        $dto->isBot = $isBot;

        return $dto;
    }
}
