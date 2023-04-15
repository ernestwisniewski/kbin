<?php

declare(strict_types=1);

namespace App\MessageHandler\Notification;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\Contracts\FavouriteInterface;
use App\Factory\MagazineFactory;
use App\Message\Notification\FavouriteNotificationMessage;
use App\Service\GenerateHtmlClassService;
use App\Service\VotableRepositoryResolver;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SentFavouriteNotificationHandler
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly MagazineFactory $magazineFactory,
        private readonly VotableRepositoryResolver $resolver,
        private readonly HubInterface $publisher,
        private readonly GenerateHtmlClassService $classService,
    ) {
    }

    public function __invoke(FavouriteNotificationMessage $message): void
    {
        $repo = $this->resolver->resolve($message->subjectClass);
        $this->notifyMagazine($repo->find($message->subjectId));
    }

    private function notifyMagazine(FavouriteInterface $subject): void
    {
        try {
            $iri = $this->iriConverter->getIriFromResource($this->magazineFactory->createDto($subject->magazine));

            $update = new Update(
                ['pub', $iri],
                $this->getNotification($subject)
            );

            $this->publisher->publish($update);
        } catch (\Exception $e) {
        }
    }

    private function getNotification(FavouriteInterface $fav): string
    {
        $subject = explode('\\', get_class($fav));

        return json_encode(
            [
                'op' => end($subject).'Favourite',
                'id' => $fav->getId(),
                'htmlId' => $this->classService->fromEntity($fav),
                'count' => $fav->favouriteCount,
            ]
        );
    }
}
