<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('people')]
class PeopleComponent
{
    public ?Magazine $magazine = null;

    public function __construct(
        private Environment $twig,
        private CacheInterface $cache,
        private MagazineRepository $magazineRepository,
        private UserRepository $userRepository,
        private PostRepository $postRepository
    ) {
    }

    public function getHtml(): string
    {
        if ($this->magazine) {
            return $this->magazine();
        } else {
            return $this->general();
        }
    }

    private function magazine(): string
    {
        return $this->cache->get(
            'magazine_people_'.$this->magazine->getId(),
            function (ItemInterface $item) {
                $item->expiresAfter(3600);

                $local = $this->postRepository->findUsers($this->magazine);
                $federated = $this->postRepository->findUsers($this->magazine, true);

                return $this->twig->render(
                    'magazine/_people.html.twig',
                    [
                        'magazine' => $this->magazine,
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

    private function general(): string
    {
        return $this->cache->get(
            'people',
            function (ItemInterface $item) {
                $item->expiresAfter(60);

                return $this->twig->render(
                    'people/_people.html.twig',
                    [
                        'magazines' => array_filter(
                            $this->magazineRepository->findByActivity(),
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
