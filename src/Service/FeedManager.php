<?php declare(strict_types=1);

namespace App\Service;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Entry;
use App\Factory\EntryFactory;
use App\PageView\EntryPageView;
use App\Repository\Criteria;
use App\Repository\EntryRepository;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use ArrayIterator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use FeedIo\Feed;
use FeedIo\Feed\Item;
use FeedIo\FeedInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class FeedManager
{
    public function __construct(
        private SettingsManager $settings,
        private EntryRepository $entryRepository,
        private MagazineRepository $magazineRepository,
        private UserRepository $userRepository,
        private RouterInterface $router,
        private EntryFactory $entryFactory,
        private IriConverterInterface $iriConverter,
        EntityManagerInterface $entityManager
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

        if ($id === 'sub') {
            $criteria->subscribed = true;
        } elseif ($id === 'mod') {
            $criteria->moderated = true;
        }

        $items = $this->entryRepository->findByCriteria($criteria);

        if ($items->count() === 0) {
            throw new NotFoundHttpException();
        }

        $items = $this->getEntries($items->getCurrentPageResults());

        foreach ($items as $item) {
            $feed->add($item);
        }

        return $feed;
    }

    public function getEntries(ArrayIterator $entries): \Generator
    {
        foreach ($entries as $entry) {
            /**
             * @var $entry Entry
             */
            $item = new Item;
            $item->setTitle($entry->title);
            $item->setLastModified(DateTime::createFromImmutable($entry->createdAt));
            $item->setLink(
                'https://'.$this->settings->get('KBIN_DOMAIN').
                $this->router->generate('entry_single', [
                    'magazine_name' => $entry->magazine->name,
                    'entry_id'      => $entry->getId(),
                    'slug'          => $entry->slug,
                ])
            );
            $item->setPublicId($this->iriConverter->getIriFromItem($this->entryFactory->createDto($entry)));
            $item->setAuthor((new Item\Author())->setName($entry->user->username));
            yield $item;
        }
    }

    private function createFeed(): Feed
    {
        $feed = new Feed();
        $feed->setTitle($this->settings->get('KBIN_META_TITLE'));
        $feed->setDescription($this->settings->get('KBIN_META_DESCRIPTION'));
        $feed->setUrl($this->settings->get('KBIN_DOMAIN'));

        return $feed;
    }
}
