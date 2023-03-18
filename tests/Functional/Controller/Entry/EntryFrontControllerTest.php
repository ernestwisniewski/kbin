<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\DTO\ModeratorDto;
use App\Service\FavouriteManager;
use App\Service\MagazineManager;
use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class EntryFrontControllerTest extends WebTestCase
{
    public function testFrontPage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/newest');

        $this->assertSelectorTextContains('.entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.entry__meta', 'to acme');

        $this->assertSelectorTextContains('#header .active', 'Threads');

        $this->assertcount(2, $crawler->filter('.entry'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testMagazinePage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/m/acme');
        $this->assertSelectorTextContains('h2', 'Hot');

        $crawler = $client->request('GET', '/m/acme/newest');

        $this->assertSelectorTextContains('.entry__meta', 'JohnDoe');
        $this->assertSelectorTextNotContains('.entry__meta', 'to acme');

        $this->assertSelectorTextContains('#header .head-title', '/m/acme');
        $this->assertSelectorTextContains('#sidebar .magazine', 'acme');

        $this->assertSelectorTextContains('#header .active', 'Threads');

        $this->assertcount(1, $crawler->filter('.entry'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', 'Magazine title');
            $this->assertSelectorTextContains('h2', ucfirst($sortOption));
        }
    }

    public function testSubPage(): void
    {
        $client = $this->prepareEntries();

        $magazineManager = $client->getContainer()->get(MagazineManager::class);
        $magazineManager->subscribe($this->getMagazineByName('acme'), $this->getUserByUsername('Actor'));

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->request('GET', '/sub');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/sub/newest');

        $this->assertSelectorTextContains('.entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.entry__meta', 'to acme');

        $this->assertSelectorTextContains('#header .head-title', '/sub');

        $this->assertSelectorTextContains('#header .active', 'Threads');

        $this->assertcount(1, $crawler->filter('.entry'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testModPage(): void
    {
        $client = $this->prepareEntries();

        $magazineManager = $client->getContainer()->get(MagazineManager::class);
        $moderator = new ModeratorDto($this->getMagazineByName('acme'));
        $moderator->user = $this->getUserByUsername('Actor');
        $magazineManager->addModerator($moderator);

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->request('GET', '/mod');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/mod/newest');

        $this->assertSelectorTextContains('.entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.entry__meta', 'to acme');

        $this->assertSelectorTextContains('#header .head-title', '/mod');

        $this->assertSelectorTextContains('#header .active', 'Threads');

        $this->assertcount(1, $crawler->filter('.entry'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testFavPage(): void
    {
        $client = $this->prepareEntries();

        $favouriteManager = $this->getContainer()->get(FavouriteManager::class);
        $favouriteManager->toggle($this->getUserByUsername('Actor'), $this->getEntryByTitle('test entry 1'));

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->request('GET', '/fav');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/fav/newest');

        $this->assertSelectorTextContains('.entry__meta', 'JaneDoe');
        $this->assertSelectorTextContains('.entry__meta', 'to kbin');

        $this->assertSelectorTextContains('#header .head-title', '/fav');

        $this->assertSelectorTextContains('#header .active', 'Threads');

        $this->assertcount(1, $crawler->filter('.entry'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    private function prepareEntries(): KernelBrowser
    {
        $client = $this->createClient();

        $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
            null,
            $this->getMagazineByName('kbin', $this->getUserByUsername('JaneDoe')),
            $this->getUserByUsername('JaneDoe')
        );

        $this->getEntryByTitle('test entry 2');

        return $client;
    }

    private function getSortOptions(): array
    {
        return ['top', 'hot', 'newest', 'active', 'commented'];
    }
}
