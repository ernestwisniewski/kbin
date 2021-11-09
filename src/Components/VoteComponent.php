<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\VoteInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('vote')]
class VoteComponent
{
    public VoteInterface $votable;
    public string $notificationType = 'None';
    public string $baseClass;
    public string $formDest;
    public bool $hideDownvote = false;
}
