<?php declare(strict_types=1);

namespace App\PageView;

use App\Entity\Entry;
use App\Entity\Post;
use App\Repository\Criteria;

class PostCommentPageView extends Criteria
{
    public const SORT_OPTIONS = [
        self::SORT_NEW,
        self::SORT_TOP,
    ];

    private ?Post $post = null;
    private bool $onlyParents = true;

    public function isOnlyParents(): bool
    {
        return $this->onlyParents;
    }

    public function showOnlyParents(bool $onlyParents): self
    {
        $this->onlyParents = $onlyParents;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function showPost(Post $post): self
    {
        $this->post = $post;

        return $this;
    }
}
