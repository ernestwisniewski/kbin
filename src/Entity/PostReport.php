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
    private Post $subject;

    public function __construct(User $reporting, User $reported, Post $post, ?string $reason = null)
    {
        parent::__construct($reporting, $reported, $post->getMagazine(), $reason);

        $this->subject = $post;
    }

    public function getSubject(): Post
    {
        return $this->subject;
    }

    public function getType(): string
    {
        return 'post';
    }
}
