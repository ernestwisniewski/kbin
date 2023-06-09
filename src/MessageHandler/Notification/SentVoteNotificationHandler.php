<?php

declare(strict_types=1);

namespace App\MessageHandler\Notification;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\Contracts\VotableInterface;
use App\Factory\MagazineFactory;
use App\Message\Notification\VoteNotificationMessage;
use App\Service\GenerateHtmlClassService;
use App\Service\SettingsManager;
use App\Service\VotableRepositoryResolver;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SentVoteNotificationHandler
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly MagazineFactory $magazineFactory,
        private readonly VotableRepositoryResolver $resolver,
        private readonly HubInterface $publisher,
        private readonly GenerateHtmlClassService $classService,
        private readonly SettingsManager $settingsManager
    ) {
    }

    public function __invoke(VoteNotificationMessage $message): void
    {
        $repo = $this->resolver->resolve($message->subjectClass);
        $this->notifyMagazine($repo->find($message->subjectId));
    }

    private function notifyMagazine(VotableInterface $votable): void
    {
        if (false === $this->settingsManager->get('KBIN_MERCURE_ENABLED')) {
            return;
        }

        try {
            $iri = $this->iriConverter->getIriFromResource($this->magazineFactory->createDto($votable->magazine));

            $update = new Update(
                ['pub', $iri],
                $this->getNotification($votable)
            );

            $this->publisher->publish($update);
        } catch (\Exception $e) {
        }
    }

    private function getNotification(VotableInterface $votable): string
    {
        $subject = explode('\\', get_class($votable));

        return json_encode(
            [
                'op' => end($subject).'Vote',
                'id' => $votable->getId(),
                'htmlId' => $this->classService->fromEntity($votable),
                'up' => $votable->countUpVotes(),
                'down' => $votable->countDownVotes(),
            ]
        );
    }
}
