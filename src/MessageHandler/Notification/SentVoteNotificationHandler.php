<?php declare(strict_types = 1);

namespace App\MessageHandler\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Contracts\VoteInterface;
use App\Factory\MagazineFactory;
use App\Message\Notification\VoteNotificationMessage;
use App\Service\VotableRepositoryResolver;
use Exception;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentVoteNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private IriConverterInterface $iriConverter,
        private MagazineFactory $magazineFactory,
        private VotableRepositoryResolver $resolver,
        private PublisherInterface $publisher,
    ) {
    }

    public function __invoke(VoteNotificationMessage $message)
    {
        $repo = $this->resolver->resolve($message->subjectClass);
        $this->notifyMagazine($repo->find($message->subjectId));
    }

    private function notifyMagazine(VoteInterface $votable)
    {
        try {
            $iri = $this->iriConverter->getIriFromItem($this->magazineFactory->createDto($votable->magazine));

            $update = new Update(
                ['pub', $iri],
                $this->getNotification($votable)
            );

            ($this->publisher)($update);

        } catch (Exception $e) {
        }
    }

    private function getNotification(VoteInterface $votable)
    {
        $subject = explode('\\', get_class($votable));

        return json_encode(
            [
                'op' => end($subject).'Vote',
                'id' => $votable->getId(),
                'up' => $votable->countUpVotes(),
                'down' => $votable->countDownVotes(),
            ]
        );
    }
}
