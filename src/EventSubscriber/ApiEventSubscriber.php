<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\ApiDataProvider\DtoPaginator;
use App\DTO\EntryDto;
use App\Factory\ImageFactory;
use App\Factory\MagazineFactory;
use App\Factory\UserFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MagazineFactory $magazineFactory,
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
        }
    }

    private function collection(DtoPaginator $dtos)
    {
        foreach ($dtos->getIterator() as $dto) {
            switch ($dto) {
                case $dto instanceof EntryDto:
                    $this->entry($dto);
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
}
