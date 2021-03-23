<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\MessageNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageNotificationRepository::class)
 */
class MessageNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="notifications")
     */
    private ?Message $message;

    public function __construct(
        User $receiver,
        Message $message
    ) {
        parent::__construct($receiver);

        $this->message = $message;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getSubject(): Message
    {
        return $this->message;
    }

    public function getType(): string
    {
        return 'message_notification';
    }
}
