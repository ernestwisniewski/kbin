<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\Notification;

use App\Entity\Contracts\FavouriteInterface;
use App\Kbin\Factory\HtmlClassFactory;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Message\Notification\FavouriteNotificationMessage;
use App\Service\SettingsManager;
use App\Service\VotableRepositoryResolver;
use App\Utils\IriGenerator;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SentFavouriteNotificationHandler
{
    public function __construct(
        private readonly MagazineFactory $magazineFactory,
        private readonly VotableRepositoryResolver $resolver,
        private readonly HubInterface $publisher,
        private readonly HtmlClassFactory $classService,
        private readonly SettingsManager $settingsManager
    ) {
    }

    public function __invoke(FavouriteNotificationMessage $message): void
    {
        $repo = $this->resolver->resolve($message->subjectClass);
        $this->notifyMagazine($repo->find($message->subjectId));
    }

    private function notifyMagazine(FavouriteInterface $subject): void
    {
        if (false === $this->settingsManager->get('KBIN_MERCURE_ENABLED')) {
            return;
        }

        try {
            $iri = IriGenerator::getIriFromResource($subject->magazine);

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
        $subject = explode('\\', \get_class($fav));

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
