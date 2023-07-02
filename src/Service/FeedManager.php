<?php

declare(strict_types=1);

namespace App\Service;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\Entry;
use App\Factory\EntryFactory;
use App\PageView\EntryPageView;
use App\Repository\Criteria;
use App\Repository\EntryRepository;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use FeedIo\Feed;
use FeedIo\Feed\Item;
use FeedIo\Feed\Node\Category;
use FeedIo\FeedInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class FeedManager
{
    public function __construct(
        private readonly SettingsManager $settings,
        private readonly EntryRepository $entryRepository,
        private readonly MagazineRepository $magazineRepository,
        private readonly UserRepository $userRepository,
        private readonly RouterInterface $router,
        private readonly EntryFactory $entryFactory,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function getFeed(Request $request): FeedInterface
    {
        $id = $request->get('id');

        $feed = $this->createFeed();

        $criteria = (new EntryPageView(1))->showSortOption(Criteria::SORT_NEW);

        if ($magazine = $request->get('magazine')) {
            $criteria->magazine = $this->magazineRepository->findOneBy(['name' => $magazine]);
        }

        if ($user = $request->get('user')) {
            $criteria->user = $this->userRepository->findOneByUsername($user);
        }

        if ($domain = $request->get('domain')) {
            $criteria->setDomain($domain);
        }

        if ($tag = $request->get('tag')) {
            $criteria->tag = $tag;
        }

        if ($sortBy = $request->get('sortBy')) {
            $criteria->showSortOption($sortBy);
        }

        if ('sub' === $id) {
            $criteria->subscribed = true;
        } elseif ('mod' === $id) {
            $criteria->moderated = true;
        }

        $items = $this->entryRepository->findByCriteria($criteria);

        if (0 === $items->count()) {
            throw new NotFoundHttpException();
        }

        $items = $this->getEntries($items->getCurrentPageResults());

        foreach ($items as $item) {
            $feed->add($item);
        }

        return $feed;
    }

    private function createFeed(): Feed
    {
        $feed = new Feed();
        $feed->setTitle($this->settings->get('KBIN_META_TITLE'));
        $feed->setDescription($this->settings->get('KBIN_META_DESCRIPTION'));
        $feed->setUrl($this->settings->get('KBIN_DOMAIN'));

        return $feed;
    }

    public function getEntries(\ArrayIterator $entries): \Generator
    {
        /** @var $entry Entry */
        foreach ($entries as $entry) {
            $link = 'https://' . $this->settings->get('KBIN_DOMAIN') .
                $this->router->generate('entry_single', [
                    'magazine_name' => $entry->magazine->name,
                    'entry_id' => $entry->getId(),
                    'slug' => $entry->slug,
                ]);

            $item = new Item();
            $item->setTitle($entry->title);
            $item->setContent($entry->getShortDesc());
            $item->setLastModified(\DateTime::createFromImmutable($entry->createdAt));
            $item->setLink($link);
            $item->set('comments', $link.'#comments');
            $item->setPublicId($this->iriConverter->getIriFromResource($this->entryFactory->createDto($entry)));
            $item->setAuthor((new Item\Author())->setName($entry->user->username));

            foreach ($entry->getTags() as $tag) {
                $category = new Category();
                $category->setLabel($tag);

                $item->addCategory($category);
            }

            yield $item;
        }
    }
}
