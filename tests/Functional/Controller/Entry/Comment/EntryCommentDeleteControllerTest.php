<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\Entity\EntryComment;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EntryCommentDeleteControllerTest extends WebTestCase
{
    public function testCanDeleteEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');

        $comment  = $this->createEntryComment('example comment');
        $comment2 = $this->createEntryComment('test');
        $child1   = $this->createEntryComment('child', null, $user2, $comment);
        $child2   = $this->createEntryComment('child2', null, null, $child1);

        $this->createVote(1, $comment, $user2);
        $this->createVote(1, $comment2, $user2);
        $this->createVote(1, $child1, $user2);

        $entryUrl = "/m/acme/t/{$child1->entry->getId()}/-";
        $client->request('GET', '/');
        $client->request('GET', $entryUrl);

        $crawler = $client->request('GET', "{$entryUrl}/komentarz/{$comment->getId()}/edytuj");
        $client->submit(
            $crawler->filter('.kbin-comment-wrapper')->selectButton('usuń')->form()
        );
        $client->followRedirect();

        $crawler = $client->request('GET', "{$entryUrl}/komentarz/{$comment2->getId()}/edytuj");
        $client->submit(
            $crawler->filter('.kbin-comment-wrapper')->selectButton('usuń')->form()
        );
        $client->followRedirect();

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

    public function testCanPurgeEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $moderator        = $this->getUserByUsername('JohnDoe');
        $moderator->roles = ['ROLE_ADMIN'];

        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $manager->persist($moderator);
        $manager->flush();

        $repo = $manager->getRepository(EntryComment::class);

        $user2 = $this->getUserByUsername('JaneDoe');
        $user3 = $this->getUserByUsername('MaryJane');

        $comment  = $this->createEntryComment('example comment', null, $user2);
        $comment2 = $this->createEntryComment('test', null, $user2);
        $child1   = $this->createEntryComment('child', null, $user3, $comment);
        $child2   = $this->createEntryComment('child2', null, null, $child1);

        $this->createVote(1, $comment, $user3);
        $this->createVote(1, $child1, $user2);
        $this->createVote(1, $child2, $user2);

        $this->assertSame(4, $repo->count([]));

        $client->request('GET', "/");
        $crawler = $client->request('GET', "/m/acme/t/{$child1->entry->getId()}/-");

        $client->submit(
            $crawler->filter('blockquote#'.$comment->getId())->selectButton('wyczyść')->form()
        );

        $this->assertSame(1, $repo->count([]));
    }

    public function testUnauthorizedUserCannotPurgeEntryComment(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));
        $client->catchExceptions(false);
        $comment = $this->createEntryComment('example comment');

        $entryUrl = "/m/acme/t/{$comment->entry->getId()}/-";

        $client->request('GET', '/');
        $crawler = $client->request('GET', $entryUrl.'/komentarze');

        $this->assertEmpty($crawler->filter('.kbin-entry-meta')->selectLink('edytuj'));
        $this->assertSelectorTextContains('blockquote', 'example comment');

        $client->request('GET', "{$entryUrl}/komentarz/{$comment->getId()}/edytuj");

        $this->assertTrue($client->getResponse()->isForbidden());
    }

    public function testCanRestoreEntryComment(): void
    {
        $client = $this->createClient();

        $client->loginUser($moderator = $this->getUserByUsername('moderator'));

        $this->getMagazineByName('acme', $moderator);

        $user3 = $this->getUserByUsername('testUser');

        $comment  = $this->createEntryComment('example comment');
        $comment2 = $this->createEntryComment('test');
        $child1   = $this->createEntryComment('child', null, $user3, $comment);
        $child2   = $this->createEntryComment('child2', null, null, $child1);

        $client->request('GET', '/');
        $crawler = $client->request('GET', "/m/acme/t/{$comment->entry->getId()}/-");

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
