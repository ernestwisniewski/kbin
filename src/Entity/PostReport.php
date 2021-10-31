<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PostReport extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="reports")
     */
    public ?Post $post;

    public function __construct(User $reporting, Post $post, ?string $reason = null)
    {
        parent::__construct($reporting, $post->user, $post->magazine, $reason);

        $this->post = $post;
    }

    public function getSubject(): Post
    {
        return $this->post;
    }

    public function clearSubject(): Report
    {
        $this->post = null;

        return $this;
    }

    public function getType(): string
    {
        return 'post';
    }
}
