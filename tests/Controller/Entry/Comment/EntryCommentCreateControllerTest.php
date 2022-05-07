<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry\Comment;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EntryCommentCreateControllerTest extends WebTestCase
{
    public function testCanCreateEntryComment()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('title');

        $crawler = $client->request('GET', '/m/polityka/t/'.$entry->getId().'/-/komentarze');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_comment[body]' => 'przykladowa tresc',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('blockquote', 'przykladowa tresc');
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine .kbin-magazine-stats-links', 'Komentarze 1');
        $this->assertSelectorTextContains('.kbin-entry .kbin-entry-meta', '1 komentarz');
    }

    public function testCanCreateNestedComments()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('testowy wpis');
        $user1 = $this->getUserByUsername('regularUser');
        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');

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


    public function testXmlCanReplyEntryComment()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $comment = $this->createEntryComment('przykładowy komentarz');

        $crawler = $client->request('GET', "/m/polityka/t/{$comment->entry->getId()}/-/komentarze");

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

        $client->click($crawler->filter('blockquote.kbin-comment')->selectLink('odpowiedz')->link());

        $this->assertStringContainsString('kbin-comment-create-form', $client->getResponse()->getContent());
    }
}
