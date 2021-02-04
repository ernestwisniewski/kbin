<?php declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EntryCommentControllerTest extends WebTestCase
{
    public function testCanCreateEntryComment()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('title');

        $crawler = $client->request('GET', $entryUrl = '/m/polityka/t/'.$entry->getId());

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

        $entryUrl = "/m/polityka/t/{$comment->getEntry()->getId()}";

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

        $this->assertResponseRedirects($entryUrl);

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('blockquote', 'zmieniona treść');
    }

    public function testCanPurgeEntryComment()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $comment  = $this->createEntryComment('przykładowy komentarz');
        $comment2 = $this->createEntryComment('test', $comment->getEntry());

        $entryUrl = "/m/polityka/t/{$comment->getEntry()->getId()}";

        $crawler = $client->request('GET', "{$entryUrl}/komentarz/{$comment->getId()}/edytuj");

        $client->submit(
            $crawler->selectButton('Usuń')->form()
        );
        $this->assertResponseRedirects($entryUrl);

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('blockquote', 'przykładowy komentarz');
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine .kbin-magazine-stats-links', 'Komentarze 1');
        $this->assertSelectorTextContains('.kbin-entry .kbin-entry-meta', '1 komentarzy');

    }

    public function testUnauthorizedUserCannotPurgeEntryComment()
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));
        $client->catchExceptions(false);
        $comment  = $this->createEntryComment('przykładowy komentarz');

        $entryUrl = "/m/polityka/t/{$comment->getEntry()->getId()}";

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', $entryUrl);

        $this->assertEmpty($crawler->filter('.kbin-entry-meta')->selectLink('edytuj'));
        $this->assertSelectorTextContains('blockquote', 'przykładowy komentarz');

        $crawler = $client->request('GET', "{$entryUrl}/komentarz/{$comment->getId()}/edytuj");

        $this->assertTrue($client->getResponse()->isForbidden());
    }
}
