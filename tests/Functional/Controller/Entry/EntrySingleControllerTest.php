<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Entity\Contracts\VoteInterface;
use App\Service\FavouriteManager;
use App\Service\VoteManager;
use App\Tests\WebTestCase;

class EntrySingleControllerTest extends WebTestCase
{
    public function testUserCanGoToEntryFromFrontpage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getEntryByTitle('test entry 1');

        $crawler = $client->request('GET', '/');
        $client->click($crawler->selectLink('test entry 1')->link());

        $this->assertSelectorTextContains('article h1', 'test entry 1');
        $this->assertSelectorTextContains('#kbin-main', 'No comments');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-entry-info', 'Thread');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-magazine', 'Magazine');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-user-list', 'Moderators');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-posts', 'Related posts');
    }

    public function testUserCanSeeArticle(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $entry = $this->getEntryByTitle('test entry 1', null, 'Test entry content');

        $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $this->assertSelectorTextContains('article h1', 'test entry 1');
        $this->assertSelectorNotExists('article h1 a');
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
        $entry = $this->getEntryByTitle('test entry 1');

        $manager = static::getContainer()->get(VoteManager::class);
        $manager->vote(VoteInterface::VOTE_DOWN, $entry, $this->getUserByUsername('JaneDoe'));

        $manager = static::getContainer()->get(FavouriteManager::class);
        $manager->toggle($this->getUserByUsername('JohnDoe'), $entry);
        $manager->toggle($this->getUserByUsername('JaneDoe'), $entry);

        $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $this->assertSelectorTextContains('.kbin-options-activity', 'Activity (3)');
    }
}
