<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentEditControllerTest extends WebTestCase
{
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

    public function testXmlCanEditEntryComment()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $comment = $this->createEntryComment('przykładowy komentarz');

        $crawler = $client->request('GET', "/m/polityka/t/{$comment->entry->getId()}/-/komentarze");

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

        $client->click($crawler->filter('blockquote.kbin-comment')->selectLink('edytuj')->link());

        $this->assertStringContainsString('kbin-comment-create-form', $client->getResponse()->getContent());
    }
}
