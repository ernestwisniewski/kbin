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
                        'local' => $this->sort(
                            $this->userRepository->findBy(['id' => array_map(fn($val) => $val['id'], $local)]),
                            $local
                        ),
                        'federated' => $this->sort(
                            $this->userRepository->findBy(
                                ['id' => array_map(fn($val) => $val['id'], $federated)]
                            ),
                            $federated
                        ),
                    ]
                );
            }
        );
    }

    private function sort(array $users, array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $result[] = array_values(array_filter($users, fn($val) => $val->getId() === $id['id']))[0];
        }

        return array_values($result);
    }
}
