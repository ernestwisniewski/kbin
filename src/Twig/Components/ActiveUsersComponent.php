<?php

namespace App\Twig\Components;

use App\Entity\Magazine;
use App\Repository\UserRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('active_users', template: 'components/_cached.html.twig')]
final class ActiveUsersComponent
{
    public ?Magazine $magazine = null;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CacheInterface $cache,
        private readonly Environment $twig
    ) {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        return $this->render();
    }

    private function render(): string
    {
        return $this->cache->get('active_users_'.$this->magazine?->getId(), function (ItemInterface $item) {
            $item->expiresAfter(60);

            return $this->twig->render(
                'components/active_users.html.twig',
                [
                    'users' => $this->userRepository->findActiveUsers($this->magazine),
                ]
            );
        });
    }
}
