<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class MessageNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="notifications")
     */
    public ?Message $message;

    public function __construct(
        User $receiver,
        Message $message
    ) {
        parent::__construct($receiver);

        $this->message = $message;
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
