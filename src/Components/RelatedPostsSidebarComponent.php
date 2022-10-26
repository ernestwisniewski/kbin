<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('related_posts_sidebar')]
class RelatedPostsSidebarComponent
{
    const RELATED_LIMIT = 4;
    const TYPE_TAG = 'tag';
    const TYPE_MAGAZINE = 'magazine';
    const TYPE_RANDOM = 'random';

    public string $tag = '';
    public string $type = self::TYPE_TAG;
    public ?Post $post = null;

    public function __construct(
        private PostRepository $repository,
        private Environment $twig,
        private Security $security,
        private CacheInterface $cache
    ) {
    }

    public function getHtml(): string
    {
        return $this->cache->get(
            'related_posts_sidebar_'.$this->type.'_'.$this->tag.'_'.$this->security->getUser()?->getId(),
            function (ItemInterface $item) {
                $item->expiresAfter(60);

                $posts = match ($this->type) {
                    self::TYPE_TAG => $this->repository->findRelatedByTag($this->tag, self::RELATED_LIMIT + 20),
                    self::TYPE_MAGAZINE => $this->repository->findRelatedByMagazine($this->tag, self::RELATED_LIMIT + 20),
                    default => $this->repository->findLast(self::RELATED_LIMIT + 20),
                };

                if ($this->post) {
                    $posts = array_filter($posts, fn($e) => $e->getId() !== $this->post->getId());
                }

                if (!count($posts)) {
                    return '';
                }

                if (count($posts) > self::RELATED_LIMIT) {
                    shuffle($posts); // randomize the order
                    $posts = array_slice($posts, 0, self::RELATED_LIMIT);
                }

                return $this->twig->render('post/_related_sidebar.html.twig', ['posts' => $posts]);
            }
        );
    }
}
