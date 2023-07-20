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
    public function testRootPage(): void
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

    public function testXmlRootPage(): void
    {
        $client = $this->createClient();

        $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', '/');

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
    }

    public function testXmlRootPageIsFrontPage(): void
    {
        $client = $this->createClient();

        $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', '/');

        $root_content = $this->clearTokens($client->getResponse()->getContent());

        $client->request('GET', '/all');

        $this->assertSame($root_content, $this->clearTokens($client->getResponse()->getContent()));
    }

    public function testFrontPage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/all');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/all/newest');

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

    public function testXmlFrontPage(): void
    {
        $client = $this->createClient();

        $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', '/all');

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
    }

    public function testMagazinePage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/m/acme');
        $this->assertSelectorTextContains('h2', 'Hot');

        $client->request('GET', '/m/ACME');
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

    public function testXmlMagazinePage(): void
    {
        $client = $this->createClient();

        $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', '/m/acme/newest');

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
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

    public function testXmlSubPage(): void
    {
        $client = $this->createClient();

        $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $magazineManager = $client->getContainer()->get(MagazineManager::class);
        $magazineManager->subscribe($this->getMagazineByName('acme'), $this->getUserByUsername('Actor'));

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', '/sub');

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
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

    public function testXmlModPage(): void
    {
        $client = $this->createClient();

        $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $magazineManager = $client->getContainer()->get(MagazineManager::class);
        $moderator = new ModeratorDto($this->getMagazineByName('acme'));
        $moderator->user = $this->getUserByUsername('Actor');
        $magazineManager->addModerator($moderator);

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', '/mod');

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
    }

    public function testFavPage(): void
    {
        $client = $this->prepareEntries();

        $favouriteManager = $this->getService(FavouriteManager::class);
        $favouriteManager->toggle(
            $this->getUserByUsername('Actor'),
            $this->getEntryByTitle('test entry 1', 'https://kbin.pub')
        );

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

    public function testXmlFavPage(): void
    {
        $client = $this->createClient();

        $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $favouriteManager = $this->getService(FavouriteManager::class);
        $favouriteManager->toggle(
            $this->getUserByUsername('Actor'),
            $this->getEntryByTitle('test entry 1', 'https://kbin.pub')
        );

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', '/fav');

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
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

        $this->getEntryByTitle('test entry 2', 'https://kbin.pub');

        return $client;
    }

    private function getSortOptions(): array
    {
        return ['top', 'hot', 'newest', 'active', 'commented'];
    }

    private function clearTokens(string $responseContent): string
    {
        return preg_replace(
            '#name="token" value=".+"#',
            '',
            json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR),
        )['html'];
    }
}
