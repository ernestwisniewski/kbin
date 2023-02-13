<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

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

        $this->assertSelectorTextContains('.kbin-post header', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-post header', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-active', 'Microblog');

        $this->assertcount(2, $crawler->filter('.kbin-post'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.kbin-options__sort')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.kbin-options__sort', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testMagazinePage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/m/acme/microblog');
        $this->assertSelectorTextContains('h2', 'Hot');

        $crawler = $client->request('GET', '/m/acme/microblog/newest');

        $this->assertSelectorTextContains('.kbin-post header', 'JohnDoe');
        $this->assertSelectorTextNotContains('.kbin-post header', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/m/acme');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-magazine', 'acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-active', 'Microblog');

        $this->assertcount(1, $crawler->filter('.kbin-post'));

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

        $client->request('GET', '/sub/microblog');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/sub/microblog/newest');

        $this->assertSelectorTextContains('.kbin-post header', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-post header', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/sub');

        $this->assertSelectorTextContains('#kbin-header .kbin-active', 'Microblog');

        $this->assertcount(1, $crawler->filter('.kbin-post'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.kbin-options__sort')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.kbin-options__sort', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testModPage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/mod/microblog');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/mod/microblog/newest');

        $this->assertSelectorTextContains('.kbin-post header', 'JohnDoe');
        $this->assertSelectorTextContains('.kbin-post header', 'to acme');

        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/mod');

        $this->assertSelectorTextContains('#kbin-header .kbin-active', 'Microblog');

        $this->assertcount(1, $crawler->filter('.kbin-post'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.kbin-options__sort')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.kbin-options__sort', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testFavPage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/fav/microblog');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/fav/microblog/newest');

//        @todo
//        $this->assertSelectorTextContains('.kbin-entry__meta', 'JaneDoe');
//        $this->assertSelectorTextContains('.kbin-entry__meta', 'to kbin');
//
        $this->assertSelectorTextContains('#kbin-header .kbin-magazine', '/fav');

        $this->assertSelectorTextContains('#kbin-header .kbin-active', 'Microblog');

        $this->assertcount(0, $crawler->filter('.kbin-post'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.kbin-options__sort')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.kbin-options__sort', $sortOption);
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

        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->createPost('test post 2');

        return $client;
    }

    private function getSortOptions(): array
    {
        return ['top', 'hot', 'newest', 'active', 'commented'];
    }
}
