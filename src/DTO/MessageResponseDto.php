<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Message;
use App\Kbin\User\DTO\UserSmallResponseDto;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class MessageResponseDto implements \JsonSerializable
{
    public ?UserSmallResponseDto $sender = null;
    public ?string $body = null;
    #[OA\Property(enum: Message::STATUS_OPTIONS, default: Message::STATUS_NEW)]
    public ?string $status = null;
    public ?int $threadId = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?int $messageId = null;

    // public function __construct(Message $message)
    // {
    //     $this->sender = new UserSmallResponseDto($message->sender);
    //     $this->body = $message->body;
    //     $this->status = $message->status;
    //     $this->threadId = $message->thread->getId();
    //     $this->createdAt = $message->createdAt;
    //     $this->messageId = $message->getId();
    // }

    public static function create(UserSmallResponseDto $sender, string $body, string $status, int $threadId, \DateTimeImmutable $createdAt, int $messageId): self
    {
        $dto = new MessageResponseDto();
        $dto->sender = $sender;
        $dto->body = $body;
        $dto->status = $status;
        $dto->threadId = $threadId;
        $dto->createdAt = $createdAt;
        $dto->messageId = $messageId;

        return $dto;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'messageId' => $this->messageId,
            'threadId' => $this->threadId,
            'sender' => $this->sender?->jsonSerialize(),
            'body' => $this->body,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
