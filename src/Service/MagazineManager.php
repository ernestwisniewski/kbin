<?php

declare(strict_types=1);

namespace App\Service;

use ApiPlatform\Api\UrlGeneratorInterface;
use App\DTO\MagazineBanDto;
use App\DTO\MagazineDto;
use App\DTO\MagazineThemeDto;
use App\DTO\ModeratorDto;
use App\Entity\Magazine;
use App\Entity\Moderator;
use App\Entity\User;
use App\Event\Magazine\MagazineBanEvent;
use App\Event\Magazine\MagazineBlockedEvent;
use App\Event\Magazine\MagazineSubscribedEvent;
use App\Factory\MagazineFactory;
use App\Repository\MagazineSubscriptionRepository;
use App\Repository\MagazineSubscriptionRequestRepository;
use App\Service\ActivityPub\KeysGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Webmozart\Assert\Assert;

class MagazineManager
{
    public function __construct(
        private readonly MagazineFactory $factory,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly RateLimiterFactory $magazineLimiter,
        private readonly CacheInterface $cache,
        private readonly EntityManagerInterface $entityManager,
        private readonly MagazineSubscriptionRequestRepository $requestRepository,
        private readonly MagazineSubscriptionRepository $subscriptionRepository,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function create(MagazineDto $dto, User $user, bool $limiter = true): Magazine
    {
        if ($limiter) {
            $limiter = $this->magazineLimiter->create($dto->ip);
            if (false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $magazine = $this->factory->createFromDto($dto, $user);
        $magazine->apId = $dto->apId;
        $magazine->apProfileId = $dto->apProfileId;

        if (!$dto->apId) {
            $magazine = KeysGenerator::generate($magazine);
            $magazine->apProfileId = $this->urlGenerator->generate(
                'ap_magazine',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABS_URL
            );
        }

        $this->entityManager->persist($magazine);
        $this->entityManager->flush();

        $this->subscribe($magazine, $user);

        return $magazine;
    }

    public function acceptFollow(User $user, Magazine $magazine): void
    {
        if ($request = $this->requestRepository->findOneby(['user' => $user, 'magazine' => $magazine])) {
            $this->entityManager->remove($request);
        }

        if ($this->subscriptionRepository->findOneBy(['user' => $user, 'magazine' => $magazine])) {
            return;
        }

        $this->subscribe($magazine, $user, false);
    }

    public function subscribe(Magazine $magazine, User $user, $createRequest = true): void
    {
        $user->unblockMagazine($magazine);

//        if ($magazine->apId && $createRequest) {
//            if ($this->requestRepository->findOneby(['user' => $user, 'magazine' => $magazine])) {
//                return;
//            }
//
//            $request = new MagazineSubscriptionRequest($user, $magazine);
//            $this->entityManager->persist($request);
//            $this->entityManager->flush();
//
//            $this->dispatcher->dispatch(new MagazineSubscribedEvent($magazine, $user));
//
//            return;
//        }

        $magazine->subscribe($user);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new MagazineSubscribedEvent($magazine, $user));
    }

    public function edit(Magazine $magazine, MagazineDto $dto): Magazine
    {
        Assert::same($magazine->name, $dto->name);

        $magazine->title = $dto->title;
        $magazine->description = $dto->description;
        $magazine->rules = $dto->rules;
        $magazine->isAdult = $dto->isAdult;

        $this->entityManager->flush();

        return $magazine;
    }

    public function delete(Magazine $magazine): void
    {
        $magazine->softDelete();

        $this->entityManager->flush();
    }

    public function purge(Magazine $magazine): void
    {
        $this->entityManager->remove($magazine);
        $this->entityManager->flush();
    }

    public function createDto(Magazine $magazine): MagazineDto
    {
        return $this->factory->createDto($magazine);
    }

    public function block(Magazine $magazine, User $user): void
    {
        $this->unsubscribe($magazine, $user);

        $user->blockMagazine($magazine);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new MagazineBlockedEvent($magazine, $user));
    }

    public function unsubscribe(Magazine $magazine, User $user): void
    {
        $magazine->unsubscribe($user);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new MagazineSubscribedEvent($magazine, $user, true));
    }

    public function unblock(Magazine $magazine, User $user): void
    {
        $user->unblockMagazine($magazine);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new MagazineBlockedEvent($magazine, $user));
    }

    public function ban(Magazine $magazine, User $user, User $bannedBy, MagazineBanDto $dto): void
    {
        Assert::greaterThan($dto->expiredAt, new \DateTime());

        $ban = $magazine->addBan($user, $bannedBy, $dto->reason, $dto->expiredAt);

        if (!$ban) {
            return;
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new MagazineBanEvent($ban));
    }

    public function unban(Magazine $magazine, User $user): void
    {
        if (!$magazine->isBanned($user)) {
            return;
        }

        $ban = $magazine->unban($user);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new MagazineBanEvent($ban));
    }

    public function addModerator(ModeratorDto $dto): void
    {
        $magazine = $dto->magazine;

        $magazine->addModerator(new Moderator($magazine, $dto->user, false, true));

        $this->entityManager->flush();

        $this->clearCommentsCache($dto->user);
    }

    private function clearCommentsCache(User $user)
    {
        $this->cache->invalidateTags([
            'post_comments_user_'.$user->getId(),
            'entry_comments_user_'.$user->getId(),
        ]);
    }

    public function removeModerator(Moderator $moderator): void
    {
        $user = $moderator->user;

        $this->entityManager->remove($moderator);
        $this->entityManager->flush();

        $this->clearCommentsCache($user);
    }

    public function changeTheme(MagazineThemeDto $dto): Magazine
    {
        $magazine = $dto->magazine;

        $magazine->icon = $dto->icon ?? $magazine->icon;

        $background = null;
        $customCss = null;

        // Background
        if ($dto->backgroundImage) {
            $background = match ($dto->backgroundImage) {
                'shape1' => 'https://karab.in/build/images/shape.png',
                'shape2' => 'https://karab.in/build/images/shape2.png',
                default => null,
            };
            $background = $background ? "#middle { background: url($background); height: 100%; }" : null;
        }

        if ($background || $customCss) {
            $magazine->customCss = ($background ?? '').($customCss ?? '');
        } else {
            if ($dto->customCss) {
                $magazine->customCss = $dto->customCss;
            } else {
                $magazine->customCss = null;
            }
        }

        // $magazine->customJs = $dto->customJs;

        $this->entityManager->persist($magazine);
        $this->entityManager->flush();

        return $magazine;
    }
}
