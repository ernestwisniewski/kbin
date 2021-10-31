<?php declare(strict_types = 1);

namespace App\EventSubscriber\Api;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\ApiDataProvider\DtoPaginator;
use App\DTO\EntryCommentDto;
use App\DTO\EntryDto;
use App\DTO\MagazineDto;
use App\DTO\PostCommentDto;
use App\DTO\PostDto;
use App\Factory\DomainFactory;
use App\Factory\EntryFactory;
use App\Factory\ImageFactory;
use App\Factory\MagazineFactory;
use App\Factory\PostFactory;
use App\Factory\UserFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MagazineFactory $magazineFactory,
        private EntryFactory $entryFactory,
        private PostFactory $postFactory,
        private UserFactory $userFactory,
        private ImageFactory $imageFactory,
        private DomainFactory $domainFactory
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['transform', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function transform(ViewEvent $event): void
    {
        if (!$event->getControllerResult()) {
            return;
        }

        switch ($dto = $event->getControllerResult()) {
            case $dto instanceof DtoPaginator:
                $this->collection($dto);
                break;
            case $dto instanceof EntryDto:
                $this->entry($dto);
                break;
            case $dto instanceof EntryCommentDto:
                $this->entryComment($dto);
                break;
            case $dto instanceof PostDto:
                $this->post($dto);
                break;
            case $dto instanceof PostCommentDto:
                $this->postComment($dto);
                break;
            case $dto instanceof MagazineDto:
                $this->magazine($dto);
                break;
        }
    }

    private function collection(DtoPaginator $dtos)
    {
        foreach ($dtos->getIterator() as $dto) {
            switch ($dto) {
                case $dto instanceof EntryDto:
                    $this->entry($dto);
                    break;
                case $dto instanceof EntryCommentDto:
                    $this->entryComment($dto);
                    break;
                case $dto instanceof PostDto:
                    $this->post($dto);
                    break;
                case $dto instanceof PostCommentDto:
                    $this->postComment($dto);
                    break;
                case $dto instanceof MagazineDto:
                    $this->magazine($dto);
                    break;
            }
        }
    }

    private function entry(EntryDto $dto): void
    {
        $dto->magazine     = $this->magazineFactory->createDto($dto->magazine);
        $dto->user         = $this->userFactory->createDto($dto->user);
        $dto->user->avatar = $dto->user->avatar ? $this->imageFactory->createDto($dto->user->avatar) : null;
        $dto->image        = $dto->image ? $this->imageFactory->createDto($dto->image) : null;
        $dto->domain       = $dto->domain ? $this->domainFactory->createDto($dto->domain) : null;
    }

    private function entryComment(EntryCommentDto $dto): void
    {
        $dto->magazine     = $this->magazineFactory->createDto($dto->magazine);
        $dto->user         = $this->userFactory->createDto($dto->user);
        $dto->user->avatar = $dto->user->avatar ? $this->imageFactory->createDto($dto->user->avatar) : null;
        $dto->entry        = $this->entryFactory->createDto($dto->entry);
        $dto->image        = $dto->image ? $this->imageFactory->createDto($dto->image) : null;
    }

    private function post(PostDto $dto): void
    {
        $dto->magazine     = $this->magazineFactory->createDto($dto->magazine);
        $dto->user         = $this->userFactory->createDto($dto->user);
        $dto->user->avatar = $dto->user->avatar ? $this->imageFactory->createDto($dto->user->avatar) : null;
        $dto->image        = $dto->image ? $this->imageFactory->createDto($dto->image) : null;
    }

    private function postComment(PostCommentDto $dto): void
    {
        $dto->magazine     = $this->magazineFactory->createDto($dto->magazine);
        $dto->user         = $this->userFactory->createDto($dto->user);
        $dto->user->avatar = $dto->user->avatar ? $this->imageFactory->createDto($dto->user->avatar) : null;
        $dto->post         = $this->postFactory->createDto($dto->post);
        $dto->post->user   = $this->userFactory->createDto($dto->post->user);
        $dto->image        = $dto->image ? $this->imageFactory->createDto($dto->image) : null;
    }

    private function magazine(MagazineDto $dto): void
    {
        $dto->user  = $this->userFactory->createDto($dto->user);
        $dto->cover = $dto->cover ? $this->imageFactory->createDto($dto->cover) : null;
    }
}
