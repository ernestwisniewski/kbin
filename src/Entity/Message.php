<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\MessageRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 */
class Message
{
    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';

    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="MessageThread", inversedBy="messages", cascade={"persist"})
     */
    private MessageThread $thread;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     */
    private User $sender;

    /**
     * @ORM\Column(type="text")
     */
    private string $body;

    public function __construct(MessageThread $thread, User $sender, string $body)
    {
        $this->thread = $thread;
        $this->sender = $sender;
        $this->body   = $body;

        $thread->addMessage($this);

        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getThread(): MessageThread
    {
        return $this->thread;
    }

    public function getSender(): User
    {
        return $this->sender;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
