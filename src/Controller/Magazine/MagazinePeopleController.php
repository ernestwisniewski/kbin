<?php declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MagazinePeopleController extends AbstractController
{
    public function __construct(
        private PostRepository $postRepository,
        private UserRepository $userRepository,
        private CacheInterface $cache
    ) {
    }

    public function __invoke(Magazine $magazine, ?string $category, Request $request): Response
    {
        return $this->cache->get(
            'magazine_people_'.$magazine->getId(),
            function (ItemInterface $item) use ($magazine) {
                $item->expiresAfter(3600);

                $local = $this->postRepository->findUsers($magazine);
                $federated = $this->postRepository->findUsers($magazine, true);

                return $this->render(
                    'magazine/people.html.twig',
                    [
                        'magazine' => $magazine,
                        'local' => $this->userRepository->findBy(['id' => array_map(fn($val) => $val['id'], $local)]),
                        'federated' => $this->userRepository->findBy(
                            ['id' => array_map(fn($val) => $val['id'], $federated)]
                        ),
                    ]
                );
            }
        );
    }
}
