<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class MessageThreadResponseDto implements \JsonSerializable
{
    #[OA\Property(type: 'array', description: 'Users in thread', items: new OA\Items(ref: '#/components/schemas/UserResponseDto'))]
    public ?array $participants = null;
    public ?int $messageCount = null;
    #[OA\Property(type: 'array', description: 'Messages in thread', items: new OA\Items(ref: '#/components/schemas/MessageResponseDto'))]
    public ?array $messages = null;
    public ?int $threadId = null;

    public static function create(array $participants, int $messageCount, array $messages, int $threadId): self
    {
        $dto = new MessageThreadResponseDto();
        $dto->participants = $participants;
        $dto->messageCount = $messageCount;
        $dto->messages = $messages;
        $dto->threadId = $threadId;

        return $dto;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'threadId' => $this->threadId,
            'participants' => $this->participants,
            'messageCount' => $this->messageCount,
            'messages' => $this->messages,
        ];
    }
}
