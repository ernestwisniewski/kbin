<?php

declare(strict_types=1);

namespace App\Schema;

use App\Entity\Notification;
use App\Kbin\Magazine\DTO\MagazineBanResponseDto;
use App\Repository\NotificationRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class NotificationSchema
{
    #[OA\Property(description: 'The id of the notification')]
    public int $notificationId = 0;
    #[OA\Property(
        description: 'The notification type',
        enum: [
            'entry_created_notification',
            'entry_edited_notification',
            'entry_deleted_notification',
            'entry_mentioned_notification',
            'entry_comment_created_notification',
            'entry_comment_edited_notification',
            'entry_comment_reply_notification',
            'entry_comment_deleted_notification',
            'entry_comment_mentioned_notification',
            'post_created_notification',
            'post_edited_notification',
            'post_deleted_notification',
            'post_mentioned_notification',
            'post_comment_created_notification',
            'post_comment_edited_notification',
            'post_comment_reply_notification',
            'post_comment_deleted_notification',
            'post_comment_mentioned_notification',
            'message_notification',
            'ban_notification',
        ]
    )]
    public string $type = 'entry_created_notification';
    #[OA\Property(description: 'The notification\'s status', enum: NotificationRepository::STATUS_OPTIONS)]
    public string $status = Notification::STATUS_NEW;
    #[OA\Property(type: 'object', oneOf: [
        new OA\Schema(ref: '#/components/schemas/EntryResponseDto'),
        new OA\Schema(ref: '#/components/schemas/EntryCommentResponseDto'),
        new OA\Schema(ref: '#/components/schemas/PostResponseDto'),
        new OA\Schema(ref: '#/components/schemas/PostCommentResponseDto'),
        new OA\Schema(ref: '#/components/schemas/MessageResponseDto'),
        new OA\Schema(ref: new Model(type: MagazineBanResponseDto::class)),
    ])]
    public mixed $subject = null;
}
