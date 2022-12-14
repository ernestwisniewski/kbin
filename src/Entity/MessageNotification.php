<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class MessageNotification extends Notification
{
    #[ManyToOne(targetEntity: Message::class, inversedBy: 'notifications')]
    #[JoinColumn(nullable: true)]
    public ?Message $message = null;

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
