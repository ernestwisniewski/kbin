<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Repository\MagazineLogEntryDeleteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MagazineLogPostDeleteRepository")
 */
class MagazineLogPostDelete extends MagazineLog
{
    /**
     * @ORM\ManyToOne(targetEntity="Post")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?Post $post;

    public function __construct(Post $post, User $user)
    {
        parent::__construct($post->getMagazine(), $user);

        $this->post = $post;
    }

    public function getType(): string
    {
        return 'log_post_delete';
    }

    public function getPost(): Post
    {
        return $this->post;
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
