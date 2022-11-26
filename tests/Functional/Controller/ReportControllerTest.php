<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    public function testCanAddEntryReport(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');
        $this->getEntryByTitle('testowy wpis', null, null, null, $user2);

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-entry-list .kbin-entry-meta')->selectLink('zgłoś')->link());

        $client->submit(
            $crawler->filter('form[name=report]')->selectButton('Gotowe')->form(
                [
                    'report[reason]' => 'Przykładowy report',
                ]
            )
        );

        $this->assertReportPanel($client);
    }

    public function assertReportPanel($client): void
    {
        $client->followRedirect();
        $crawler = $client->request('GET', '/m/acme/najnowsze');
        $client->click($crawler->filter('.kbin-sidebar')->selectLink('Zgłoszenia')->link());

        $this->assertSelectorTextContains('.kbin-magazine-bans', 'Przykładowy report');
    }

    public function testCanAddEntryCommentReport(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');

        $comment = $this->createEntryComment('example comment', null, $user2);

        $crawler = $client->request('GET', '/m/acme/t/'.$comment->entry->getId().'/-/komentarze');
        $crawler = $client->click($crawler->filter('.kbin-comment')->selectLink('zgłoś')->link());

        $client->submit(
            $crawler->filter('form[name=report]')->selectButton('Gotowe')->form(
                [
                    'report[reason]' => 'Przykładowy report',
                ]
            )
        );

        $this->assertReportPanel($client);
    }

    public function testCanAddPostReport(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');

        $post = $this->createPost('example post', null, $user2);

        $crawler = $client->request('GET', '/m/acme/w/'.$post->getId());
        $crawler = $client->click($crawler->filter('.kbin-post')->selectLink('zgłoś')->link());

        $client->submit(
            $crawler->filter('form[name=report]')->selectButton('Gotowe')->form(
                [
                    'report[reason]' => 'Przykładowy report',
                ]
            )
        );

        $this->assertReportPanel($client);
    }

    public function testCanAddPostCommentReport(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');

        $post = $this->createPost('example post', null, $user2);
        $this->createPostComment('example comment', $post, $user2);

        $crawler = $client->request('GET', '/m/acme/w/'.$post->getId());
        $crawler = $client->click($crawler->filter('.kbin-comment')->selectLink('zgłoś')->link());

        $client->submit(
            $crawler->filter('form[name=report]')->selectButton('Gotowe')->form(
                [
                    'report[reason]' => 'Przykładowy report',
                ]
            )
        );

        $this->assertReportPanel($client);
    }
}
