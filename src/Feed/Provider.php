<?php declare(strict_types=1);

namespace App\Feed;

use App\Service\FeedManager;
use Debril\RssAtomBundle\Provider\FeedProviderInterface;
use FeedIo\FeedInterface;
use Symfony\Component\HttpFoundation\Request;

class Provider implements FeedProviderInterface
{
    public function __construct(private FeedManager $manager)
    {
    }

    public function getFeed(Request $request): FeedInterface
    {
        return $this->manager->getFeed($request);
    }

    protected function getItems(): \Generator
    {
        yield $this->manager->getItems();
    }
}
