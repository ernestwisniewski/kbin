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

    public function __construct(User $reporting, User $reported, Post $post)
    {
        parent::__construct($reporting, $reported, $post->getMagazine());

        $this->post = $post;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function getType(): string
    {
        return 'post';
    }
}
