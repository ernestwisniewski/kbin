<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\MagazineBanDto;
use App\DTO\MagazineThemeDto;
use App\DTO\ModeratorDto;
use App\Entity\Moderator;
use App\Event\MagazineBanEvent;
use App\Event\MagazineBlockedEvent;
use App\Event\MagazineSubscribedEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Factory\MagazineFactory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;
use App\DTO\MagazineDto;
use App\Entity\Magazine;
use App\Entity\User;

class MagazineManager
{
    private MagazineFactory $magazineFactory;
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $entityManager;

    public function __construct(MagazineFactory $magazineFactory, EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager)
    {
        $this->magazineFactory = $magazineFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager   = $entityManager;
    }

    public function create(MagazineDto $magazineDto, User $user): Magazine
    {
        $magazine = $this->magazineFactory->createFromDto($magazineDto, $user);

        $this->entityManager->persist($magazine);
        $this->entityManager->flush();

        $this->subscribe($magazine, $user);

        return $magazine;
    }

    public function edit(Magazine $magazine, MagazineDto $magazineDto): Magazine
    {
        Assert::same($magazine->getName(), $magazineDto->getName());

        $magazine->setTitle($magazineDto->getTitle());
        $magazine->setDescription($magazineDto->getDescription());
        $magazine->setRules($magazineDto->getRules());

        $this->entityManager->flush();

        return $magazine;
    }

    public function delete(Magazine $magazine): void
    {
        if ($magazine->getEntryCount() > 10) {
            $magazine->softDelete();
        } else {
            $this->purge($magazine);

            return;
        }

        $this->entityManager->flush();
    }

    public function purge(Magazine $magazine): void
    {
        $this->entityManager->remove($magazine);
        $this->entityManager->flush();
    }

    public function createDto(Magazine $magazine): MagazineDto
    {
        return ($this->magazineFactory->createDto($magazine))->setId($magazine->getId());
    }

    public function subscribe(Magazine $magazine, User $user): void
    {
        $user->unblockMagazine($magazine);

        $magazine->subscribe($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineSubscribedEvent($magazine, $user));
    }

    public function unsubscribe(Magazine $magazine, User $user): void
    {
        $magazine->unsubscribe($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineSubscribedEvent($magazine, $user));
    }

    public function block(Magazine $magazine, User $user): void
    {
        $this->unsubscribe($magazine, $user);

        $user->blockMagazine($magazine);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineBlockedEvent($magazine, $user));
    }

    public function unblock(Magazine $magazine, User $user): void
    {
        $user->unblockMagazine($magazine);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineBlockedEvent($magazine, $user));
    }

    public function ban(Magazine $magazine, User $user, User $bannedBy, MagazineBanDto $dto): void
    {
        Assert::greaterThan($dto->getExpiredAt(), new \DateTime());

        $magazine->addBan($user, $bannedBy, $dto->getReason(), $dto->getExpiredAt());

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineBanEvent($magazine, $user));
    }


    public function unban(Magazine $magazine, User $user): void
    {
        if (!$magazine->isBanned($user)) {
            return;
        }

        $magazine->unban($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineBanEvent($magazine, $user));
    }

    public function addModerator(ModeratorDto $dto): void
    {
        $magazine = $dto->getMagazine();

        $magazine->addModerator(new Moderator($magazine, $dto->getUser(), false, true));

        $this->entityManager->flush();
    }

    public function removeModerator(Moderator $moderator): void
    {
        $this->entityManager->remove($moderator);
        $this->entityManager->flush();
    }

    public function changeTheme(MagazineThemeDto $dto)
    {
        $magazine = $dto->getMagazine();

        $magazine->setCover($dto->getCover());

        $this->entityManager->flush();
    }
}
