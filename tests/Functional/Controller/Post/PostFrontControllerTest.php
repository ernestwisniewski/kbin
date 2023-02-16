<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\DTO\ModeratorDto;
use App\Service\FavouriteManager;
use App\Service\MagazineManager;
use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class PostFrontControllerTest extends WebTestCase
{
    public function testFrontPage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/microblog');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/microblog/newest');

        $this->assertSelectorTextContains('.post header', 'JohnDoe');
        $this->assertSelectorTextContains('.post header', 'to acme');

        $this->assertSelectorTextContains('#header .active', 'Microblog');

        $this->assertcount(2, $crawler->filter('.post'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testMagazinePage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/m/acme/microblog');
        $this->assertSelectorTextContains('h2', 'Hot');

        $crawler = $client->request('GET', '/m/acme/microblog/newest');

        $this->assertSelectorTextContains('.post header', 'JohnDoe');
        $this->assertSelectorTextNotContains('.post header', 'to acme');

        $this->assertSelectorTextContains('#header .magazine', '/m/acme');
        $this->assertSelectorTextContains('#sidebar .magazine', 'acme');

        $this->assertSelectorTextContains('#header .active', 'Microblog');

        $this->assertcount(1, $crawler->filter('.post'));

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

        $magazineManager = $this->getContainer()->get(MagazineManager::class);
        $magazineManager->subscribe($this->getMagazineByName('acme'), $this->getUserByUsername('Actor'));

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->request('GET', '/sub/microblog');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/sub/microblog/newest');

        $this->assertSelectorTextContains('.post header', 'JohnDoe');
        $this->assertSelectorTextContains('.post header', 'to acme');

        $this->assertSelectorTextContains('#header .magazine', '/sub');

        $this->assertSelectorTextContains('#header .active', 'Microblog');

        $this->assertcount(1, $crawler->filter('.post'));

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

        $client->request('GET', '/mod/microblog');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/mod/microblog/newest');

        $this->assertSelectorTextContains('.post header', 'JohnDoe');
        $this->assertSelectorTextContains('.post header', 'to acme');

        $this->assertSelectorTextContains('#header .magazine', '/mod');

        $this->assertSelectorTextContains('#header .active', 'Microblog');

        $this->assertcount(1, $crawler->filter('.post'));

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
        $favouriteManager->toggle($this->getUserByUsername('Actor'), $this->createPost('test post 3'));

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->request('GET', '/fav/microblog');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/fav/microblog/newest');

        $this->assertSelectorTextContains('.post header', 'JohnDoe');
        $this->assertSelectorTextContains('.post header', 'to acme');

        $this->assertSelectorTextContains('#header .magazine', '/fav');

        $this->assertSelectorTextContains('#header .active', 'Microblog');

        $this->assertcount(1, $crawler->filter('.post'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    private function prepareEntries(): KernelBrowser
    {
        $client = $this->createClient();

        $this->createPost(
            'test post 1',
            $this->getMagazineByName('kbin', $this->getUserByUsername('JaneDoe')),
            $this->getUserByUsername('JaneDoe')
        );

        $this->createPost('test post 2');

        return $client;
    }

    private function getSortOptions(): array
    {
        return ['top', 'hot', 'newest', 'active', 'commented'];
    }
}
