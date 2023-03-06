<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentEditControllerTest extends WebTestCase
{
    public function testAuthorCanEditOwnEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');
        $this->createEntryComment('test comment 1', $entry);

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $crawler = $client->click($crawler->filter('#main .entry-comment')->selectLink('edit')->link());

        $this->assertSelectorExists('#main .entry-comment');

        $this->assertSelectorTextContains('#main .entry-comment', 'test comment 1');

        $client->submit(
            $crawler->filter('form[name=entry_comment]')->selectButton('Edit comment')->form(
                [
                    'entry_comment[body]' => 'test comment 2 body',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .entry-comment', 'test comment 2 body');
    }
}
