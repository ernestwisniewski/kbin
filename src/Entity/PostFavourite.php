<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PostFavourite extends Favourite
{
    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="favourites")
     */
    public ?Post $post;

    public function __construct(User $user, Post $post)
    {
        parent::__construct($user);

        $this->magazine = $post->magazine;
        $this->post     = $post;
    }

    public function getSubject(): Post
    {
        return $this->post;
    }

    public function clearSubject(): Favourite
    {
        $this->post = null;

        return $this;
    }

    public function getType(): string
    {
        return 'post';
    }
}
