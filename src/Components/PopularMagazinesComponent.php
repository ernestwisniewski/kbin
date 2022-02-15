<?php declare(strict_types=1);

namespace App\Components;

use App\Repository\MagazineRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('popular_magazines')]
class PopularMagazinesComponent
{
    public function __construct(private MagazineRepository $repository)
    {
    }

    public function getMagazines()
    {
        return $this->repository->findBy([], ['subscriptionsCount' => 'DESC'], 26);
    }
}
