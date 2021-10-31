<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class MagazineLogPostDeleted extends MagazineLog
{
    /**
     * @ORM\ManyToOne(targetEntity="Post")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Post $post;

    public function __construct(Post $post, User $user)
    {
        parent::__construct($post->magazine, $user);

        $this->post = $post;
    }

    public function getType(): string
    {
        return 'log_post_deleted';
    }

    public function getSubject(): ContentInterface
    {
        return $this->post;
    }

    public function clearSubject(): MagazineLog
    {
        $this->post = null;

        return $this;
    }
}
