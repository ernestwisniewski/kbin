<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    public function testCanAddEntryReport()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');
        $this->getEntryByTitle('testowy wpis', null, null, null, $user2);

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-entry-list .kbin-entry-meta')->selectLink('zgłoś')->link());

        $client->submit(
            $crawler->filter('.kbin-report-page')->selectButton('Gotowe')->form(
                [
                    'report[reason]' => 'Przykładowy report',
                ]
            )
        );

        $this->assertReportPanel($client);
    }

    public function assertReportPanel($client)
    {
        $crawler = $client->followRedirect();
        $crawler = $client->request('GET', '/m/polityka/najnowsze');
        $crawler = $client->click($crawler->filter('.kbin-sidebar')->selectLink('Zgłoszenia')->link());

        $this->assertSelectorTextContains('.kbin-magazine-bans', 'Przykładowy report');
    }

    public function testCanAddEntryCommentReport()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');

        $comment = $this->createEntryComment('przykładowy komentarz', null, $user2);

        $crawler = $client->request('GET', '/m/polityka/t/'.$comment->entry->getId().'/-/komentarze');
        $crawler = $client->click($crawler->filter('.kbin-comment')->selectLink('zgłoś')->link());

        $client->submit(
            $crawler->filter('.kbin-report-page')->selectButton('Gotowe')->form(
                [
                    'report[reason]' => 'Przykładowy report',
                ]
            )
        );

        $this->assertReportPanel($client);
    }

    public function testCanAddPostReport()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');

        $post = $this->createPost('przykładowy post', null, $user2);

        $crawler = $client->request('GET', '/m/polityka/w/'.$post->getId());
        $crawler = $client->click($crawler->filter('.kbin-post')->selectLink('zgłoś')->link());

        $client->submit(
            $crawler->filter('.kbin-report-page')->selectButton('Gotowe')->form(
                [
                    'report[reason]' => 'Przykładowy report',
                ]
            )
        );

        $this->assertReportPanel($client);
    }

    public function testCanAddPostCommentReport()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');

        $post    = $this->createPost('przykładowy post', null, $user2);
        $comment = $this->createPostComment('przykładowy komentarz', $post, $user2);

        $crawler = $client->request('GET', '/m/polityka/w/'.$post->getId());
        $crawler = $client->click($crawler->filter('.kbin-comment')->selectLink('zgłoś')->link());

        $client->submit(
            $crawler->filter('.kbin-report-page')->selectButton('Gotowe')->form(
                [
                    'report[reason]' => 'Przykładowy report',
                ]
            )
        );

        $this->assertReportPanel($client);
    }
}
