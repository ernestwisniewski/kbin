<?php

declare(strict_types=1);

namespace App\Command\Update\Async;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Post;
use App\Entity\PostComment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
readonly class NoteVisibilityHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $client,
    ) {
    }

    public function __invoke(NoteVisibilityMessage $message)
    {
        $repo = $this->entityManager->getRepository($message->class);

        /**
         * @var $entity Post|PostComment
         */
        $entity = $repo->find($message->id);
        $req = $this->client->request('GET', $entity->apId, [
            'headers' => [
                'Accept' => 'application/activity+json,application/ld+json,application/json',
            ],
        ]);

        if (Response::HTTP_NOT_FOUND === $req->getStatusCode()) {
            $entity->visibility = VisibilityInterface::VISIBILITY_PRIVATE;
            $this->entityManager->flush();
        }
    }
}
