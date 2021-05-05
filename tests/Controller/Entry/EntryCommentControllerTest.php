<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EntryCommentControllerTest extends WebTestCase
{
    public function testCanCreateEntryComment()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('title');

        $crawler = $client->request('GET', $entryUrl = '/m/polityka/t/'.$entry->getId().'/title/komentarze');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_comment[body]' => 'przykladowa tresc',
                ]
            )
        );

        $this->assertResponseRedirects($entryUrl);

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('blockquote', 'przykladowa tresc');
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine .kbin-magazine-stats-links', 'Komentarze 1');
        $this->assertSelectorTextContains('.kbin-entry .kbin-entry-meta', '1 komentarzy');
    }

    public function testCanEditEntryComment()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $comment = $this->createEntryComment('przykładowy komentarz');

        $entryUrl = "/m/polityka/t/{$comment->entry->getId()}/-/komentarze";

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', $entryUrl);
        $crawler = $client->click($crawler->filter('.kbin-comment-meta-list-item a')->selectLink('edytuj')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_comment[body]' => 'zmieniona treść',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('blockquote', 'zmieniona treść');
    }

    public function testCanPurgeEntryComment()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');

        $comment  = $this->createEntryComment('przykładowy komentarz');
        $comment2 = $this->createEntryComment('test', $comment->entry);

        $this->createVote(1, $comment, $user2);
        $this->createVote(1, $comment2, $user2);

        $entryUrl = "/m/polityka/t/{$comment->entry->getId()}/-";

        $crawler = $client->request('GET', "{$entryUrl}/komentarz/{$comment->getId()}/edytuj");

        $client->submit(
            $crawler->selectButton('Usuń')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('blockquote', '[Treść usunięta przez użytkownika]');

        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine .kbin-magazine-stats-links', 'Komentarze 1');
        $this->assertSelectorTextContains('.kbin-entry .kbin-entry-meta', '1 komentarzy');
    }

    public function testUnauthorizedUserCannotPurgeEntryComment()
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));
        $client->catchExceptions(false);
        $comment = $this->createEntryComment('przykładowy komentarz');

        $entryUrl = "/m/polityka/t/{$comment->entry->getId()}/-";

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', $entryUrl.'/komentarze');

        $this->assertEmpty($crawler->filter('.kbin-entry-meta')->selectLink('edytuj'));
        $this->assertSelectorTextContains('blockquote', 'przykładowy komentarz');

        $crawler = $client->request('GET', "{$entryUrl}/komentarz/{$comment->getId()}/edytuj");

        $this->assertTrue($client->getResponse()->isForbidden());
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

        $crawler = $client->click($crawler->filter('.kbin-comment-level--2')->selectLink('odpowiedz')->link());
        $this->assertSelectorTextContains('.kbin-comment-wrapper', 'odpowiedz');

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

    public function testCanPurgeNestedComments()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('testowy wpis');
        $user1 = $this->getUserByUsername('regularUser');
        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');

        $comment1 = $this->createEntryComment('komentarz 1', $entry, $user1);
        $comment2 = $this->createEntryComment('komentarz 2', $entry, $user2, $comment1);
        $comment3 = $this->createEntryComment('komentarz 3', $entry, $user3, $comment2);

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-entry-list .kbin-entry-title')->selectLink('testowy wpis')->link());

        $crawler = $client->click($crawler->filter('.kbin-comment--top-level')->selectLink('edytuj')->link());
        $this->assertSelectorTextContains('.kbin-comment-wrapper', 'odpowiedz');

        $client->submit(
            $crawler->selectButton('Usuń')->form()
        );

        $this->assertResponseRedirects();

        $crawler = $client->followRedirect();

        // @todo soft delete
//        $this->assertSelectorNotExists('.kbin-comment--top-level');
//        $this->assertSelectorNotExists('.kbin-comment-level--2');
//        $this->assertSelectorNotExists('.kbin-comment-level--3');
    }
}
