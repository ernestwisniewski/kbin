<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\ApiDataProvider\DtoPaginator;
use App\DTO\EntryCommentDto;
use App\DTO\EntryDto;
use App\DTO\PostCommentDto;
use App\DTO\PostDto;
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
        private IriConverterInterface $iriConverter,
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
            }
        }
    }

    private function entry(EntryDto $dto): void
    {
        $dto->magazine = $this->magazineFactory->createDto($dto->magazine);
        $dto->user     = $this->userFactory->createDto($dto->user);
        $dto->image    = $dto->image ? $this->imageFactory->createDto($dto->image) : null;
    }

    private function entryComment(EntryCommentDto $dto): void
    {
        $dto->magazine = $this->magazineFactory->createDto($dto->magazine);
        $dto->user     = $this->userFactory->createDto($dto->user);
        $dto->entry    = $this->entryFactory->createDto($dto->entry);
        $dto->image    = $dto->image ? $this->imageFactory->createDto($dto->image) : null;
    }

    private function post(PostDto $dto): void
    {
        $dto->magazine = $this->magazineFactory->createDto($dto->magazine);
        $dto->user     = $this->userFactory->createDto($dto->user);
        $dto->image    = $dto->image ? $this->imageFactory->createDto($dto->image) : null;
    }

    private function postComment(PostCommentDto $dto): void
    {
        $dto->magazine = $this->magazineFactory->createDto($dto->magazine);
        $dto->user     = $this->userFactory->createDto($dto->user);
        $dto->post     = $this->postFactory->createDto($dto->post);
        $dto->image    = $dto->image ? $this->imageFactory->createDto($dto->image) : null;
    }
}
