<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Repository\MagazineLogEntryDeleteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MagazineLogPostCommentDeleteRepository")
 */
class MagazineLogPostCommentDelete extends MagazineLog
{
    /**
     * @ORM\ManyToOne(targetEntity="PostComment")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?PostComment $postComment;

    public function __construct(PostComment $comment, User $user)
    {
        parent::__construct($comment->getMagazine(), $user);

        $this->postComment = $comment;
    }

    public function getType(): string
    {
        return 'log_post_comment_delete';
    }

    public function getComment(): PostComment
    {
        return $this->postComment;

    }

    public function getSubject(): ContentInterface
    {
        return $this->postComment;
    }

    public function clearSubject(): MagazineLog
    {
        $this->postComment = null;

        return $this;
    }
}
