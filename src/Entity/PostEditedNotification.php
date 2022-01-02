<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PostEditedNotification extends Notification
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
        return 'post_edited_notification';
    }
}
