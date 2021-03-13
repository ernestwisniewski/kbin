<?php declare(strict_types=1);

namespace App\Entity;

use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use App\Entity\Contracts\ReportInterface;
use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="notification_type", type="text")
 * @ORM\DiscriminatorMap({
 *   "entry": "EntryNotification",
 *   "entry_comment": "EntryCommentNotification",
 *   "post": "PostNotification",
 *   "post_comment": "PostCommentNotification",
 * })
 */
abstract class Notification
{
    const STATUS_NEW = 'new';

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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="notifications")
     */
    private User $user;

    /**
     * @ORM\Column(type="string")
     */
    private string $status = self::STATUS_NEW;

    public function __construct(User $receiver)
    {
        $this->user = $receiver;

        $this->createdAtTraitConstruct();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    abstract public function getType(): string;
}
