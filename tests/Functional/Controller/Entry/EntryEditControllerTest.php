<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Tests\WebTestCase;

class EntryEditControllerTest extends WebTestCase
{
    public function testAuthorCanEditOwnEntryLink(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');
        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $crawler = $client->click($crawler->filter('#main .entry')->selectLink('edit')->link());

        $this->assertSelectorExists('#main .entry');

        $this->assertInputValueSame('entry_link[url]', 'https://kbin.pub');
        $this->assertEquals('disabled', $crawler->filter('#entry_link_url')->attr('disabled'));
        $this->assertEquals('disabled', $crawler->filter('#entry_link_magazine_autocomplete')->attr('disabled'));

        $client->submit(
            $crawler->filter('form[name=entry_link]')->selectButton('Edit link')->form(
                [
                    'entry_link[title]' => 'test entry 2 title',
                    'entry_link[body]' => 'test entry 2 body',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .entry header', 'test entry 2 title');
        $this->assertSelectorTextContains('#main .entry .entry__body', 'test entry 2 body');
    }

    public function testAuthorCanEditOwnEntryArticle(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', null, 'entry content test entry 1');
        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $crawler = $client->click($crawler->filter('#main .entry')->selectLink('edit')->link());

        $this->assertSelectorExists('#main .entry');

        $this->assertEquals('disabled', $crawler->filter('#entry_article_magazine_autocomplete')->attr('disabled'));

        $client->submit(
            $crawler->filter('form[name=entry_article]')->selectButton('Edit thread')->form(
                [
                    'entry_article[title]' => 'test entry 2 title',
                    'entry_article[body]' => 'test entry 2 body',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .entry header', 'test entry 2 title');
        $this->assertSelectorTextContains('#main .entry .entry__body', 'test entry 2 body');
    }
}
