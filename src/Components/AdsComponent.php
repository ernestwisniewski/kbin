<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\VoteInterface;
use App\Service\CacheService;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('ads')]
class AdsComponent
{

}
