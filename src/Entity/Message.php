<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Message
{
    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';

    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="MessageThread", inversedBy="messages", cascade={"persist"})
     */
    public MessageThread $thread;
    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     */
    public User $sender;
    /**
     * @ORM\Column(type="text")
     */
    public string $body;
    /**
     * @ORM\Column(type="string")
     */
    public string $status = self::STATUS_NEW;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;
    /**
     * @ORM\OneToMany(targetEntity="MessageNotification", mappedBy="message", cascade={"remove"}, orphanRemoval=true)
     */
    private Collection $notifications;

    public function __construct(MessageThread $thread, User $sender, string $body)
    {
        $this->thread        = $thread;
        $this->sender        = $sender;
        $this->body          = $body;
        $this->notifications = new ArrayCollection();

        $thread->addMessage($this);

        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }
}
