<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\DTO\ModeratorDto;
use App\Kbin\Magazine\MagazineSubscribe;
use App\Kbin\Magazine\Moderator\MagazineAddModerator;
use App\Service\FavouriteManager;
use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class EntryCommentFrontControllerTest extends WebTestCase
{
    public function testFrontPage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/comments');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/comments/newest');

        $this->assertSelectorTextContains('blockquote header', 'JohnDoe,');
        $this->assertSelectorTextContains('blockquote header', 'to kbin in test entry 2');
        $this->assertSelectorTextContains('blockquote .content', 'test comment 3');

        $this->assertcount(3, $crawler->filter('.comment'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testMagazinePage(): void
    {
        $client = $this->prepareEntries();

        $client->request('GET', '/m/acme/comments');
        $this->assertSelectorTextContains('h2', 'Hot');

        $crawler = $client->request('GET', '/m/acme/comments/newest');

        $this->assertSelectorTextContains('blockquote header', 'JohnDoe,');
        $this->assertSelectorTextNotContains('blockquote header', 'to acme');
        $this->assertSelectorTextContains('blockquote header', 'in test entry 1');
        $this->assertSelectorTextContains('blockquote .content', 'test comment 2');

        $this->assertSelectorTextContains('.head-title', '/m/acme');
        $this->assertSelectorTextContains('#sidebar .magazine', 'acme');

        $this->assertcount(2, $crawler->filter('.comment'));

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

        $magazineSubscribe = $this->getService(MagazineSubscribe::class);
        $magazineSubscribe($this->getMagazineByName('acme'), $this->getUserByUsername('Actor'));

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->request('GET', '/sub/comments');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/sub/comments/newest');

        $this->assertSelectorTextContains('blockquote header', 'JohnDoe,');
        $this->assertSelectorTextContains('blockquote header', 'to acme in test entry 1');
        $this->assertSelectorTextContains('blockquote .content', 'test comment 2');

        $this->assertSelectorTextContains('.head-title', '/sub');

        $this->assertcount(2, $crawler->filter('.comment'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testModPage(): void
    {
        $client = $this->prepareEntries();

        $magazineAddModerator = $this->getService(MagazineAddModerator::class);
        $moderator = new ModeratorDto($this->getMagazineByName('acme'));
        $moderator->user = $this->getUserByUsername('Actor');
        $magazineAddModerator($moderator);

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->request('GET', '/mod/comments');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/mod/comments/newest');

        $this->assertSelectorTextContains('blockquote header', 'JohnDoe,');
        $this->assertSelectorTextContains('blockquote header', 'to acme in test entry 1');
        $this->assertSelectorTextContains('blockquote .content', 'test comment 2');

        $this->assertSelectorTextContains('.head-title', '/mod');

        $this->assertcount(2, $crawler->filter('.comment'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    public function testFavPage(): void
    {
        $client = $this->prepareEntries();

        $favouriteManager = $this->getService(FavouriteManager::class);
        $favouriteManager->toggle(
            $this->getUserByUsername('Actor'),
            $this->createEntryComment('test comment 1', $this->getEntryByTitle('test entry 1'))
        );

        $client->loginUser($this->getUserByUsername('Actor'));

        $client->request('GET', '/fav/comments');
        $this->assertSelectorTextContains('h1', 'Hot');

        $crawler = $client->request('GET', '/fav/comments/newest');

        $this->assertSelectorTextContains('blockquote header', 'JohnDoe,');
        $this->assertSelectorTextContains('blockquote header', 'to acme in test entry 1');
        $this->assertSelectorTextContains('blockquote .content', 'test comment 1');

        $this->assertcount(1, $crawler->filter('.comment'));

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', ucfirst($sortOption));
        }
    }

    private function prepareEntries(): KernelBrowser
    {
        $client = $this->createClient();

        $this->createEntryComment(
            'test comment 1',
            $this->getEntryByTitle('test entry 1', 'https://kbin.pub'),
            $this->getUserByUsername('JohnDoe')
        );
        $this->createEntryComment(
            'test comment 2',
            $this->getEntryByTitle('test entry 1', 'https://kbin.pub'),
            $this->getUserByUsername('JohnDoe')
        );
        $this->createEntryComment(
            'test comment 3',
            $this->getEntryByTitle('test entry 2', 'https://kbin.pub', null, $this->getMagazineByName('kbin')),
            $this->getUserByUsername('JohnDoe')
        );

        return $client;
    }

    private function getSortOptions(): array
    {
        return ['hot', 'newest', 'active', 'oldest'];
    }
}
