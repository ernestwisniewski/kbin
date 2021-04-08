<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\MessageDto;
use App\Entity\Message;
use App\Entity\MessageThread;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class MessageManager
{
    public function __construct(
        private NotificationManager $notificationManager,
        private EntityManagerInterface $entityManager
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
            /**
             * @var $message Message
             */
            $message->setStatus(Message::STATUS_READ);

            $this->notificationManager->readMessageNotification($message, $user);
        }

        $this->entityManager->flush();
    }
}
