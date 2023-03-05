<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentCreateControllerTest extends WebTestCase
{
    public function testUserCanCreateEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1');

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->submit(
            $crawler->filter('form[name=entry_comment]')->selectButton('Add comment')->form(
                [
                    'entry_comment[body]' => 'test comment 1',
                ]
            )
        );

        $this->assertResponseRedirects('/m/acme/t/'.$entry->getId().'/test-entry-1');
        $client->followRedirect();

        $this->assertSelectorTextContains('#main blockquote', 'test comment 1');
    }

    public function testUserCanReplyEntryComment(): void
    {
        $client = $this->createClient();

        $comment = $this->createEntryComment(
            'test comment 1',
            $entry = $this->getEntryByTitle('test entry 1'),
            $this->getUserByUsername('JaneDoe')
        );

        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");
        $crawler = $client->click($crawler->filter('#entry-comment-'.$comment->getId())->selectLink('reply')->link());

        $this->assertSelectorTextContains('#main blockquote', 'test comment 1');

        $crawler = $client->submit(
            $crawler->filter('form[name=entry_comment]')->selectButton('Add comment')->form(
                [
                    'entry_comment[body]' => 'test comment 2',
                ]
            )
        );

        $this->assertResponseRedirects('/m/acme/t/'.$entry->getId().'/test-entry-1');
        $client->followRedirect();

        $this->assertEquals(2, $crawler->filter('#main blockquote')->count());
    }

    public function testUserCantCreateInvalidEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1');

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->submit(
            $crawler->filter('form[name=entry_comment]')->selectButton('Add comment')->form(
                [
                    'entry_comment[body]' => 't',
                ]
            )
        );

        $this->assertSelectorTextContains(
            '#content',
            'This value is too short. It should have 2 characters or more.'
        );
    }
}
