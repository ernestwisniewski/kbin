<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentCreateControllerTest extends WebTestCase
{
    public function testCanCreateEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('title');

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId().'/-/komentarze');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_comment[body]' => 'example content',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('blockquote', 'example content');
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine .kbin-magazine-stats-links', 'Komentarze 1');
        $this->assertSelectorTextContains('.kbin-entry .kbin-entry-meta', '1 komentarz');
    }

    public function testCanCreateNestedComments(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('testowy wpis');
        $user1 = $this->getUserByUsername('JohnDoe');
        $user2 = $this->getUserByUsername('JaneDoe');
        $user3 = $this->getUserByUsername('MaryJane');

        $comment1 = $this->createEntryComment('komentarz 1', $entry, $user1);
        $comment2 = $this->createEntryComment('komentarz 2', $entry, $user2, $comment1);

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-entry-list .kbin-entry-title')->selectLink('testowy wpis')->link());

        $this->assertSelectorTextContains('.kbin-comment--top-level', 'komentarz 1');
        $this->assertCount(1, $crawler);

        $this->assertSelectorTextContains('.kbin-comment-level--2', 'komentarz 2');
        $this->assertCount(1, $crawler->filter('.kbin-comment-level--2'));

        $this->assertSelectorTextContains('.kbin-comment-wrapper', 'odpowiedz');
        $crawler = $client->click($crawler->filter('.kbin-comment-level--2')->selectLink('odpowiedz')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_comment[body]' => 'komentarz poziomu 3',
                ]
            )
        );

        $this->assertResponseRedirects();

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-comment-level--3', 'komentarz poziomu 3');
        $this->assertCount(1, $crawler->filter('.kbin-comment-level--3'));
    }


    public function testXmlCanReplyEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createEntryComment('example comment');

        $crawler = $client->request('GET', "/m/acme/t/{$comment->entry->getId()}/-/komentarze");

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

        $client->click($crawler->filter('blockquote.kbin-comment')->selectLink('odpowiedz')->link());

        $this->assertStringContainsString('kbin-comment-create-form', $client->getResponse()->getContent());
    }
}
