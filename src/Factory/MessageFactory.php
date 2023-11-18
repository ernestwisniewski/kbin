<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\MessageResponseDto;
use App\DTO\MessageThreadResponseDto;
use App\Entity\Message;
use App\Entity\MessageThread;
use App\Entity\User;
use App\Kbin\User\DTO\UserResponseDto;
use App\Kbin\User\Factory\UserFactory;

class MessageFactory
{
    public function __construct(
        private readonly UserFactory $userFactory,
    ) {
    }

    public function createResponseDto(Message $message): MessageResponseDto
    {
        return MessageResponseDto::create(
            $this->userFactory->createSmallDto($message->sender),
            $message->body,
            $message->status,
            $message->thread->getId(),
            $message->createdAt,
            $message->getId()
        );
    }

    public function createThreadResponseDto(MessageThread $thread, int $depth): MessageThreadResponseDto
    {
        $participants = array_map(fn (User $participant) => new UserResponseDto($this->userFactory->createDto($participant)), $thread->participants->toArray());

        $messages = $thread->messages->toArray();
        usort($messages, fn (Message $a, Message $b) => $a->createdAt < $b->createdAt);

        $messageResponses = array_map(fn (Message $message) => $this->createResponseDto($message), $messages);

        return MessageThreadResponseDto::create(
            $participants,
            $thread->messages->count(),
            $messageResponses,
            $thread->getId()
        );
    }
}
