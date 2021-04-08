<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\PostNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostNotificationRepository::class)
 */
class PostNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="notifications")
     */
    public ?Post $post;

    public function __construct(User $receiver, Post $post)
    {
        parent::__construct($receiver);

        $this->post = $post;
    }

    public function getSubject(): Post
    {
        return $this->post;
    }

    public function getType(): string
    {
        return 'post_notification';
    }
}
