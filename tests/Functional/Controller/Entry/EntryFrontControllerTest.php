<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

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

        $this->assertSelectorTextContains('.kbin-entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-entry__meta', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-active', 'Threads');

        $this->assertcount(2, $crawler->filter('.kbin-entry'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.kbin-options__sort')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.kbin-options__sort', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testMagazinePage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/m/acme');
        $this->assertSelectorTextContains('h2', 'Hot');

        $crawler = $client->request('GET', '/m/acme/newest');

        $this->assertSelectorTextContains('.kbin-entry__meta', 'JohnDoe');
        $this->assertSelectorTextNotContains('.kbin-entry__meta', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/m/acme');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-magazine', 'acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-active', 'Threads');

        $this->assertcount(1, $crawler->filter('.kbin-entry'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.kbin-options__sort')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.kbin-options__sort', $sortOption);
            $this->assertSelectorTextContains('h1', 'Magazine title');
            $this->assertSelectorTextContains('h2', ucfirst($sortOption));
        }
    }

    public function testSubPage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/sub');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/sub/newest');

        $this->assertSelectorTextContains('.kbin-entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-entry__meta', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/sub');

        $this->assertSelectorTextContains('#kbin-header .kbin-active', 'Threads');

        $this->assertcount(1, $crawler->filter('.kbin-entry'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.kbin-options__sort')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.kbin-options__sort', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testModPage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/mod');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/mod/newest');

        $this->assertSelectorTextContains('.kbin-entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-entry__meta', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/mod');

        $this->assertSelectorTextContains('#kbin-header .kbin-active', 'Threads');

        $this->assertcount(1, $crawler->filter('.kbin-entry'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.kbin-options__sort')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.kbin-options__sort', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testFavPage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/fav');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/fav/newest');

//        @todo
//        $this->assertSelectorTextContains('.kbin-entry__meta', 'JaneDoe');
//        $this->assertSelectorTextContains('.kbin-entry__meta', 'to kbin');
//
//        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/fav');

        $this->assertSelectorTextContains('#kbin-header .kbin-active', 'Threads');

        $this->assertcount(0, $crawler->filter('.kbin-entry'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.kbin-options__sort')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.kbin-options__sort', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    private function prepareEntries(): KernelBrowser
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('JohnDoe'));

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
