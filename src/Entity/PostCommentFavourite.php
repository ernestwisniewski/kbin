<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PostCommentFavourite extends Favourite
{
    /**
     * @ORM\ManyToOne(targetEntity="PostComment", inversedBy="favourites")
     */
    public ?PostComment $postComment;

    public function __construct(User $user, PostComment $comment)
    {
        parent::__construct($user);

        $this->magazine    = $comment->magazine;
        $this->postComment = $comment;
    }

    public function getSubject(): PostComment
    {
        return $this->postComment;
    }

    public function clearSubject(): Favourite
    {
        $this->postComment = null;

        return $this;
    }

    public function getType(): string
    {
        return 'post_comment';
    }
}
