<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Message;

use App\Controller\Api\BaseApi;
use App\DTO\MessageDto;
use App\Entity\Message;
use App\Entity\MessageThread;
use App\Factory\MessageFactory;
use Symfony\Contracts\Service\Attribute\Required;

class MessageBaseApi extends BaseApi
{
    public const REPLY_DEPTH = 25;
    public const MIN_REPLY_DEPTH = 0;
    public const MAX_REPLY_DEPTH = 100;

    private MessageFactory $messageFactory;

    #[Required]
    public function setMessageFactory(MessageFactory $messageFactory): void
    {
        $this->messageFactory = $messageFactory;
    }

    /**
     * Serialize a single message to JSON.
     *
     * @param Message $message The Message to serialize
     *
     * @return array An associative array representation of the message's safe fields, to be used as JSON
     */
    protected function serializeMessage(Message $message)
    {
        $response = $this->messageFactory->createResponseDto($message);

        return $response;
    }

    /**
     * Serialize a message thread to JSON.
     *
     * @param MessageThread $thread The thread to serialize
     *
     * @return array An associative array representation of the message's safe fields, to be used as JSON
     */
    protected function serializeMessageThread(MessageThread $thread)
    {
        $depth = $this->constrainPerPage(
            $this->request->getCurrentRequest()->get('d', self::REPLY_DEPTH),
            self::MIN_REPLY_DEPTH,
            self::MAX_REPLY_DEPTH
        );
        $response = $this->messageFactory->createThreadResponseDto($thread, $depth);

        return $response;
    }

    /**
     * Deserialize a message from JSON.
     *
     * @return MessageDto A message DTO
     */
    protected function deserializeMessage(): MessageDto
    {
        $request = $this->request->getCurrentRequest();
        $dto = $this->serializer->deserialize($request->getContent(), MessageDto::class, 'json');

        return $dto;
    }
}
