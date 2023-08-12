<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Entity\Contracts\VotableInterface;
use App\Service\FavouriteManager;
use App\Service\VoteManager;
use App\Tests\WebTestCase;

class EntrySingleControllerTest extends WebTestCase
{
    public function testUserCanGoToEntryFromFrontpage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', '/');

        $this->assertSelectorTextContains('#header nav .active', 'Threads');

        $client->click($crawler->selectLink('test entry 1')->link());

        $this->assertSelectorTextContains('.head-title', '/m/acme');
        $this->assertSelectorTextContains('#header nav .active', 'Threads');
        $this->assertSelectorTextContains('article h1', 'test entry 1');
        $this->assertSelectorTextContains('#main', 'No comments');
        $this->assertSelectorTextContains('#sidebar .entry-info', 'Thread');
        $this->assertSelectorTextContains('#sidebar .magazine', 'Magazine');
        $this->assertSelectorTextContains('#sidebar .user-list', 'Moderators');
    }

    public function testUserCanSeeArticle(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', null, 'Test entry content');

        $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $this->assertSelectorTextContains('article h1', 'test entry 1');
        $this->assertSelectorNotExists('article h1 > a');
        $this->assertSelectorTextContains('article', 'Test entry content');
    }

    public function testUserCanSeeLink(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");
        $this->assertSelectorExists('article h1 a[href="https://kbin.pub"]', 'test entry 1');
    }

    public function testPostActivityCounter(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $manager = $client->getContainer()->get(VoteManager::class);
        $manager->vote(VotableInterface::VOTE_DOWN, $entry, $this->getUserByUsername('JaneDoe'));

        $manager = $client->getContainer()->get(FavouriteManager::class);
        $manager->toggle($this->getUserByUsername('JohnDoe'), $entry);
        $manager->toggle($this->getUserByUsername('JaneDoe'), $entry);

        $client->request('GET', "/m/acme/t/{$entry->getId()}/-/test-entry-1");

        $this->assertSelectorTextContains('.options-activity', 'Activity (2)');
    }

    public function testCanSortComments()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');
        $this->createEntryComment('test comment 1', $entry);
        $this->createEntryComment('test comment 2', $entry);

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");
        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
        }
    }

    private function getSortOptions(): array
    {
        return ['hot', 'newest', 'active', 'oldest'];
    }
}
