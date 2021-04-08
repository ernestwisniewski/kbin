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
    public ?Post $post;

    public function __construct(User $reporting, User $reported, Post $post, ?string $reason = null)
    {
        parent::__construct($reporting, $reported, $post->magazine, $reason);

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
