<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\BadgeDto;
use App\DTO\BadgeResponseDto;
use App\DTO\MagazineBanResponseDto;
use App\DTO\MagazineDto;
use App\DTO\MagazineLogResponseDto;
use App\DTO\MagazineResponseDto;
use App\DTO\MagazineSmallResponseDto;
use App\Entity\Badge;
use App\Entity\Magazine;
use App\Entity\MagazineBan;
use App\Entity\MagazineLog;
use App\Entity\Moderator;
use App\Entity\User;
use App\Repository\MagazineRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MagazineFactory
{
    public function __construct(
        private ImageFactory $imageFactory,
        private ModeratorFactory $moderatorFactory,
        private UserFactory $userFactory,
        private MagazineRepository $magazineRepository,
        private Security $security,
    ) {
    }

    public function createFromDto(MagazineDto $dto, User $user): Magazine
    {
        return new Magazine(
            $dto->name,
            $dto->title,
            $user,
            $dto->description,
            $dto->rules,
            $dto->isAdult,
            $dto->icon
        );
    }

    public function createDto(Magazine $magazine): MagazineDto
    {
        $dto = new MagazineDto();
        $dto->setOwner($magazine->getOwner());
        $dto->icon = $magazine->icon ? $this->imageFactory->createDto($magazine->icon) : null;
        $dto->name = $magazine->name;
        $dto->title = $magazine->title;
        $dto->description = $magazine->description;
        $dto->rules = $magazine->rules;
        $dto->subscriptionsCount = $magazine->subscriptionsCount;
        $dto->entryCount = $magazine->entryCount;
        $dto->entryCommentCount = $magazine->entryCommentCount;
        $dto->postCount = $magazine->postCount;
        $dto->postCommentCount = $magazine->postCommentCount;
        $dto->isAdult = $magazine->isAdult;
        $dto->tags = $magazine->tags;
        $dto->badges = $magazine->badges;
        $dto->moderators = $magazine->moderators;
        $dto->apId = $magazine->apId;
        $dto->apProfileId = $magazine->apProfileId;
        $dto->setId($magazine->getId());

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        // Only return the user's vote if permission to control voting has been given
        $dto->isUserSubscribed = $this->security->isGranted('ROLE_OAUTH2_MAGAZINE:SUBSCRIBE') ? $magazine->isSubscribed($currentUser) : null;
        $dto->isBlockedByUser = $this->security->isGranted('ROLE_OAUTH2_MAGAZINE:BLOCK') ? $currentUser->isBlockedMagazine($magazine) : null;

        return $dto;
    }

    public function createSmallDto(Magazine|MagazineDto $magazine): MagazineSmallResponseDto
    {
        $dto = $magazine instanceof Magazine ? $this->createDto($magazine) : $magazine;

        return new MagazineSmallResponseDto($dto);
    }

    public function createBanDto(MagazineBan $ban): MagazineBanResponseDto
    {
        return MagazineBanResponseDto::create(
            $ban->getId(),
            $ban->reason,
            $ban->expiredAt,
            $this->createSmallDto($ban->magazine),
            $this->userFactory->createSmallDto($ban->user),
            $this->userFactory->createSmallDto($ban->bannedBy),
        );
    }

    public function createLogDto(MagazineLog $log): MagazineLogResponseDto
    {
        $magazine = $this->createSmallDto($log->magazine);
        $moderator = $this->userFactory->createSmallDto($log->user);
        $createdAt = $log->createdAt;
        $type = $log->getType();
        $subject = null;
        if ('log_ban' === $type) {
            /**
             * @var MagazineLogBan $log
             */
            $subject = $this->createBanDto($log->ban);
            if ('unban' === $log->meta) {
                $type = 'log_unban';
            }
        }

        return MagazineLogResponseDto::create($magazine, $moderator, $createdAt, $type, $subject);
    }

    public function createResponseDto(MagazineDto|Magazine $magazine): MagazineResponseDto
    {
        $dto = $magazine instanceof Magazine ? $this->createDto($magazine) : $magazine;
        // Ensure that magazine is an actual magazine and not a DTO
        $magazine = $this->magazineRepository->find($magazine->getId());
        if (null === $magazine) {
            throw new NotFoundHttpException('Magazine was not found!');
        }

        return MagazineResponseDto::create(
            $this->moderatorFactory->createDtoWithUser($dto->getOwner(), $magazine),
            $dto->icon,
            $dto->name,
            $dto->title,
            $dto->description,
            $dto->rules,
            $dto->subscriptionsCount,
            $dto->entryCount,
            $dto->entryCommentCount,
            $dto->postCount,
            $dto->postCommentCount,
            $dto->isAdult,
            $dto->isUserSubscribed,
            $dto->isBlockedByUser,
            $dto->tags,
            array_map(fn (Badge|BadgeDto $badge) => new BadgeResponseDto($badge), $dto->badges?->toArray() ?? []),
            array_map(fn (Moderator $moderator) => $this->moderatorFactory->createDto($moderator), $dto->moderators?->toArray() ?? []),
            $dto->apId,
            $dto->apProfileId,
            $dto->getId(),
        );
    }

    public function createDtoFromAp(string $actorUrl, ?string $apId): MagazineDto
    {
        $dto = new MagazineDto();
        $dto->name = $apId;
        $dto->title = $apId;
        $dto->apId = $apId;
        $dto->apProfileId = $actorUrl;

        return $dto;
    }
}
