<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentEditControllerTest extends WebTestCase
{
    public function testCanEditEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createEntryComment('example comment');

        $entryUrl = "/m/acme/t/{$comment->entry->getId()}/-/komentarze";

        $client->request('GET', '/');
        $crawler = $client->request('GET', $entryUrl);
        $crawler = $client->click($crawler->filter('.kbin-comment-meta-list-item a')->selectLink('edytuj')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_comment[body]' => 'zmieniona treść',
                ]
            )
        );

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('blockquote', 'zmieniona treść');
    }

    public function testXmlCanEditEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createEntryComment('example comment');

        $crawler = $client->request('GET', "/m/acme/t/{$comment->entry->getId()}/-/komentarze");

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

        $client->click($crawler->filter('blockquote.kbin-comment')->selectLink('edytuj')->link());

        $this->assertStringContainsString('kbin-comment-create-form', $client->getResponse()->getContent());
    }
}
