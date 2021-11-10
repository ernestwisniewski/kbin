<?php declare(strict_types=1);

namespace App\Components;

use App\Repository\MagazineRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('random_magazine')]
class RandomMagazineComponent
{
    public function __construct(private MagazineRepository $repository, private Environment $twig, private CacheInterface $cache)
    {
    }

    public function getHtml(): string
    {
        return $this->cache->get('random_magazine', function (ItemInterface $item) {
            $item->expiresAfter(60);

            return $this->twig->render('_layout/_random_magazine.html.twig', [
                'magazine' => $this->repository->findRandom(),
            ]);
        });
    }
}
