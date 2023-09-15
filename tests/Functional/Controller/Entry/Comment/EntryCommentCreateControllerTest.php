<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentCreateControllerTest extends WebTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->kibbyPath = \dirname(__FILE__, 5).'/assets/kibby_emoji.png';
    }

    public function testUserCanCreateEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

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

    public function testUserCanCreateEntryCommentWithImage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $form = $crawler->filter('form[name=entry_comment]')->selectButton('Add comment')->form();
        $form->get('entry_comment[body]')->setValue('test comment 1');
        $form->get('entry_comment[image]')->upload($this->kibbyPath);
        // Needed since we require this global to be set when validating entries but the client doesn't actually set it
        $_FILES = $form->getPhpFiles();
        $client->submit($form);

        $this->assertResponseRedirects('/m/acme/t/'.$entry->getId().'/test-entry-1');
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('#main blockquote', 'test comment 1');
        $this->assertSelectorExists('blockquote footer figure img');
        $imgSrc = $crawler->filter('blockquote footer figure img')->getNode(0)->attributes->getNamedItem('src')->textContent;
        $this->assertStringContainsString(self::KIBBY_PNG_URL_RESULT, $imgSrc);
        $_FILES = [];
    }

    public function testUserCanReplyEntryComment(): void
    {
        $client = $this->createClient();

        $comment = $this->createEntryComment(
            'test comment 1',
            $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub'),
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
        $crawler = $client->followRedirect();

        $this->assertEquals(2, $crawler->filter('#main blockquote')->count());
    }

    public function testUserCantCreateInvalidEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->submit(
            $crawler->filter('form[name=entry_comment]')->selectButton('Add comment')->form(
                [
                    'entry_comment[body]' => '',
                ]
            )
        );

        $this->assertSelectorTextContains(
            '#content',
            'This value should not be blank.'
        );
    }
}
