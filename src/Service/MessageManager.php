<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\MessageDto;
use App\Entity\Message;
use App\Entity\MessageThread;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class MessageManager
{
    public function __construct(
        private readonly NotificationManager $notificationManager,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function toThread(MessageDto $dto, User $sender, User $receiver): MessageThread
    {
        $thread = new MessageThread($sender, $receiver);
        $thread->addMessage($this->toMessage($dto, $thread, $sender));

        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        return $thread;
    }

    public function toMessage(MessageDto $dto, MessageThread $thread, User $sender): Message
    {
        $message = new Message($thread, $sender, $dto->body);

        $thread->setUpdatedAt();

        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        $this->notificationManager->sendMessageNotification($message, $sender);

        return $message;
    }

    public function readMessages(MessageThread $thread, User $user): void
    {
        foreach ($thread->getNewMessages($user) as $message) {
            /*
             * @var Message $message
             */
            $this->readMessage($message, $user);
        }

        $this->entityManager->flush();
    }

    public function readMessage(Message $message, User $user, bool $flush = false): void
    {
        $message->status = Message::STATUS_READ;

        $this->notificationManager->readMessageNotification($message, $user);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function unreadMessage(Message $message, User $user, bool $flush = false): void
    {
        $message->status = Message::STATUS_NEW;

        $this->notificationManager->unreadMessageNotification($message, $user);

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
