<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry\Comment;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EntryCommentDeleteControllerTest extends WebTestCase
{
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
        $crawler  = $client->request('GET', '/');
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

    public function testCanRestoreEntryComment() {
        $client = $this->createClient();

        $client->loginUser($moderator = $this->getUserByUsername('moderator'));

        $this->getMagazineByName('polityka', $moderator);

        $user3 = $this->getUserByUsername('testUser');

        $comment = $this->createEntryComment('przykładowy komentarz');
        $comment2 = $this->createEntryComment('test');
        $child1 = $this->createEntryComment('child', null, $user3, $comment);
        $child2 = $this->createEntryComment('child2', null, null, $child1);

        $crawler  = $client->request('GET', '/');
        $crawler = $client->request('GET', "/m/polityka/t/{$comment->entry->getId()}/-");

        $client->submit(
            $crawler->filter('blockquote#'.$comment->getId())->selectButton('usuń')->form()
        );

        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->filter('blockquote#'.$child2->getId())->selectButton('usuń')->form()
        );

        $crawler = $client->followRedirect();

        $crawler = $client->click($crawler->filter('.kbin-sidebar')->selectLink('Kosz')->link());

        $this->assertCount(2, $crawler->filter('blockquote'));

        $client->submit(
            $crawler->selectButton('przywróć')->form()
        );

        $crawler = $client->followRedirect();

        $crawler = $client->click($crawler->filter('.kbin-sidebar')->selectLink('Kosz')->link());

        $this->assertCount(1, $crawler->filter('blockquote'));
    }
}
