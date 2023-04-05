<?php

declare(strict_types=1);

namespace App\MessageHandler\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Contracts\VotableInterface;
use App\Factory\MagazineFactory;
use App\Message\Notification\VoteNotificationMessage;
use App\Service\GenerateHtmlClassService;
use App\Service\VotableRepositoryResolver;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentVoteNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly MagazineFactory $magazineFactory,
        private readonly VotableRepositoryResolver $resolver,
        private readonly HubInterface $publisher,
        private readonly GenerateHtmlClassService $classService,
    ) {
    }

    public function __invoke(VoteNotificationMessage $message): void
    {
        $repo = $this->resolver->resolve($message->subjectClass);
        $this->notifyMagazine($repo->find($message->subjectId));
    }

    private function notifyMagazine(VotableInterface $votable)
    {
        try {
            $iri = $this->iriConverter->getIriFromItem($this->magazineFactory->createDto($votable->magazine));

            $update = new Update(
                ['pub', $iri],
                $this->getNotification($votable)
            );

            $this->publisher->publish($update);
        } catch (\Exception $e) {
        }
    }

    private function getNotification(VotableInterface $votable)
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
