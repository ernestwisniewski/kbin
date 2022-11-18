<?php declare(strict_types=1);

namespace App\Controller\People;

use App\Controller\AbstractController;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class FrontController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private MagazineRepository $magazineRepository,
        private CacheInterface $cache
    ) {
    }

    public function __invoke(?string $category, Request $request): Response
    {
        return $this->cache->get(
            'magazine_people_',
            function (ItemInterface $item) {
                $item->expiresAfter(60);

                return $this->render(
                    'people/front.html.twig',
                    [
                        'magazines' => array_filter(
                            $this->magazineRepository->findByActivity([], ['postCount' => 'DESC'], 100),
                            fn($val) => $val->name != 'random'
                        ),
                        'local' => $this->userRepository->findWithAbout(UserRepository::USERS_LOCAL),
                        'federated' => $this->userRepository->findWithAbout(UserRepository::USERS_REMOTE),
                    ]
                );
            }
        );
    }
}
