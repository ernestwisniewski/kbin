<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine\Panel;

use App\Tests\WebTestCase;

class MagazineTrashControllerTest extends WebTestCase
{
    public function testModCanSeeEntryInTrash(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getMagazineByName('acme');

        $entry = $this->getEntryByTitle(
            'Test entry 1',
            'https://kbin.pub',
            null,
            null,
            $this->getUserByUsername('JaneDoe')
        );

        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId().'/test-entry-1/moderate');
        $client->submit($crawler->filter('#main .moderate-panel')->selectButton('delete')->form([]));

        $client->request('GET', '/m/acme/panel/trash');
        $this->assertSelectorTextContains('#main .options__main a.active', 'trash');
        $this->assertSelectorTextContains('#main .entry', 'Test entry 1');
    }

    public function testModCanSeeEntryCommentInTrash(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getMagazineByName('acme');

        $comment = $this->createEntryComment(
            'Test comment 1',
            null,
            $this->getUserByUsername('JaneDoe')
        );

        $crawler = $client->request(
            'GET',
            '/m/acme/t/'.$comment->entry->getId().'/test-entry-1/comment/'.$comment->getId().'/moderate'
        );
        $client->submit($crawler->filter('#main .moderate-panel')->selectButton('delete')->form([]));

        $client->request('GET', '/m/acme/panel/trash');
        $this->assertSelectorTextContains('#main .comment', 'Test comment 1');
    }

    public function testModCanSeePostInTrash(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getMagazineByName('acme');

        $post = $this->createPost(
            'Test post 1',
            null,
            $this->getUserByUsername('JaneDoe')
        );

        $crawler = $client->request(
            'GET',
            '/m/acme/p/'.$post->getId().'/-/moderate'
        );
        $client->submit($crawler->filter('#main .moderate-panel')->selectButton('delete')->form([]));

        $client->request('GET', '/m/acme/panel/trash');
        $this->assertSelectorTextContains('#main .post', 'Test post 1');
    }

    public function testModCanSeePostCommentInTrash(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getMagazineByName('acme');

        $comment = $this->createPostComment(
            'Test comment 1',
            null,
            $this->getUserByUsername('JaneDoe')
        );

        $crawler = $client->request(
            'GET',
            '/m/acme/p/'.$comment->post->getId().'/test-entry-1/reply/'.$comment->getId().'/moderate'
        );
        $client->submit($crawler->filter('#main .moderate-panel')->selectButton('delete')->form([]));

        $client->request('GET', '/m/acme/panel/trash');
        $this->assertSelectorTextContains('#main .comment', 'Test comment 1');
    }

    public function testUnauthorizedUserCannotSeeTrash(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $client->request('GET', '/m/acme/panel/trash');

        $this->assertResponseStatusCodeSame(403);
    }
}
