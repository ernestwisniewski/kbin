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

    public function testCanDeleteEntryComment()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');

        $comment = $this->createEntryComment('przykładowy komentarz');
        $comment2 = $this->createEntryComment('test');
        $child1 = $this->createEntryComment('child', null, $user2, $comment);
        $child2 = $this->createEntryComment('child2', null, null, $child1);

        $this->createVote(1, $comment, $user2);
        $this->createVote(1, $comment2, $user2);
        $this->createVote(1, $child1, $user2);

        $entryUrl = "/m/polityka/t/{$child1->entry->getId()}/-";
        $crawler  = $client->request('GET', $entryUrl);
        $crawler  = $client->request('GET', $entryUrl);

        $crawler = $client->request('GET', "{$entryUrl}/komentarz/{$comment->getId()}/edytuj");
        $client->submit(
            $crawler->filter('.kbin-comment-wrapper')->selectButton('usuń')->form()
        );
        $crawler = $client->followRedirect();

        $crawler = $client->request('GET', "{$entryUrl}/komentarz/{$comment2->getId()}/edytuj");
        $client->submit(
            $crawler->filter('.kbin-comment-wrapper')->selectButton('usuń')->form()
        );
        $crawler = $client->followRedirect();

        $crawler = $client->request('GET', "{$entryUrl}");
        $client->submit(
            $crawler->filter('[data-comment-id-value]')->selectButton('usuń')->form()
        );
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('blockquote#'.$comment->getId(), '[usunięte przez autora]');
        $this->assertSelectorTextContains('blockquote#'.$child1->getId(), '[usunięte przez moderację]');
        $this->assertCount(3, $crawler->filter('.kbin-comment-content'));

        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine .kbin-magazine-stats-links', 'Komentarze 1');
        $this->assertSelectorTextContains('.kbin-entry .kbin-entry-meta', '1 komentarz');
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
}
