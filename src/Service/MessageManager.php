<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\MessageDto;
use App\Entity\Message;
use App\Entity\MessageThread;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class MessageManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function toThread(MessageDto $dto, User $sender, User $receiver): MessageThread
    {
        $thread = new MessageThread($sender, $receiver);
        $thread->addMessage($this->toMessage($dto->getBody(), $thread, $sender));

        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        return $thread;
    }

    private function toMessage(string $body, MessageThread $thread, User $sender)
    {
        return new Message($thread, $sender, $body);
    }


}
