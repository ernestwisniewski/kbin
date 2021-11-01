<?php declare(strict_types = 1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Repository\MagazineRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class MagazineRandomController extends AbstractController
{
    public function __invoke(MagazineRepository $repository)
    {
        $cache = new FilesystemAdapter();

        return $cache->get('random_magazine', function (ItemInterface $item) use ($repository) {
            $item->expiresAfter(60);

            return $this->render('_layout/_random_magazine.html.twig', [
                'magazine' => $repository->findRandom(),
            ]);
        });
    }
}
