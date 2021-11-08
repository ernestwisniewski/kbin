<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Post;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('post')]
class PostComponent
{
    public Post $post;
    public ?string $extraClass = null;
    public bool $showMagazine = true;
    public bool $showAllComments = false;
    public bool $showBestComments = false;
}
