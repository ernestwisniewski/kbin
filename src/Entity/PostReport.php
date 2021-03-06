<?php

namespace App\Entity;

use App\Repository\PostReportRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostReportRepository::class)
 */
class PostReport extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="reports")
     */
    private Post $post;

    public function __construct(User $reporting, Post $comment) {
        parent::__construct($reporting);

        $this->post = $comment;
    }

    public function getPost(): Post {
        return $this->post;
    }

    public function getType(): string {
        return 'post';
    }
}
