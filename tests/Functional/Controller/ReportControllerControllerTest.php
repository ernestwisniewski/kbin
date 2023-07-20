<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\ReportRepository;
use App\Tests\WebTestCase;

class ReportControllerControllerTest extends WebTestCase
{
    public function testLoggedUserCanReportEntry(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
            null,
            null,
            $this->getUserByUsername('JaneDoe')
        );

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");
        $crawler = $client->click($crawler->filter('#main .entry menu')->selectLink('report')->link());

        $this->assertSelectorExists('#main .entry');

        $client->submit(
            $crawler->filter('form[name=report]')->selectButton('Report')->form(
                [
                    'report[reason]' => 'test reason 1',
                ]
            )
        );

        $repo = $this->getService(ReportRepository::class);

        $this->assertEquals(1, $repo->count([]));
    }

    public function testLoggedUserCanReportEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
            null,
            null,
            $this->getUserByUsername('JaneDoe')
        );
        $this->createEntryComment('test comment 1', $entry, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");
        $crawler = $client->click($crawler->filter('#main .entry-comment')->selectLink('report')->link());

        $this->assertSelectorExists('#main .entry-comment');

        $client->submit(
            $crawler->filter('form[name=report]')->selectButton('Report')->form(
                [
                    'report[reason]' => 'test reason 1',
                ]
            )
        );

        $repo = $this->getService(ReportRepository::class);

        $this->assertEquals(1, $repo->count([]));
    }

    public function testLoggedUserCanReportPost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1', null, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");
        $crawler = $client->click($crawler->filter('#main .post menu')->selectLink('report')->link());

        $this->assertSelectorExists('#main .post');

        $client->submit(
            $crawler->filter('form[name=report]')->selectButton('Report')->form(
                [
                    'report[reason]' => 'test reason 1',
                ]
            )
        );

        $repo = $this->getService(ReportRepository::class);

        $this->assertEquals(1, $repo->count([]));
    }

    public function testLoggedUserCanReportPostComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1', null, $this->getUserByUsername('JaneDoe'));
        $this->createPostComment('test comment 1', $post, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");
        $crawler = $client->click($crawler->filter('#main .post-comment menu')->selectLink('report')->link());

        $this->assertSelectorExists('#main .post-comment');

        $client->submit(
            $crawler->filter('form[name=report]')->selectButton('Report')->form(
                [
                    'report[reason]' => 'test reason 1',
                ]
            )
        );

        $repo = $this->getService(ReportRepository::class);

        $this->assertEquals(1, $repo->count([]));
    }
}
