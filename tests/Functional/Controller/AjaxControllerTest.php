<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Service\MagazineManager;
use App\Tests\WebTestCase;

class AjaxControllerTest extends WebTestCase
{
    public function testFetchEntryArticle(): void
    {
        $client = $this->createClient();

        $entry = $this->getEntryByTitle('Lorem ipsum', null, 'dolor sit amet');

        $client->jsonRequest('GET', '/ajax/fetch_entry/'.$entry->getId());

        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }

    public function testFetchEntryLink(): void
    {
        $client = $this->createClient();

        $entry = $this->getEntryByTitle('Lorem ipsum', 'https://youtube.com');

        $client->jsonRequest('GET', '/ajax/fetch_entry/'.$entry->getId());

        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }

    public function testFetchDuplicates(): void
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('testUser'));

        $client->jsonRequest('POST', '/ajax/fetch_duplicates', [
            'url' => 'https://karab.in',
        ]);

        $this->assertStringContainsString('"total":0', $client->getResponse()->getContent());

        $this->getEntryByTitle('Lorem ipsum', 'https://karab.in');

        $client->jsonRequest('POST', '/ajax/fetch_duplicates', [
            'url' => 'https://karab.in',
        ]);

        $this->assertStringContainsString('"total":1', $client->getResponse()->getContent());
    }

    public function testFetchEntryComment(): void
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createEntryComment('Lorem ipsum');

        $client->jsonRequest('GET', '/ajax/fetch_entry_comment/'.$comment->getId());

        $this->assertStringContainsString('kbin-comment', $client->getResponse()->getContent());
        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }

    public function testFetchPost(): void
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('Lorem ipsum');

        $client->jsonRequest('GET', '/ajax/fetch_post/'.$post->getId());

        $this->assertStringContainsString('kbin-post', $client->getResponse()->getContent());
        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }

    public function testFetchPostComment(): void
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('Lorem ipsum');

        $comment = $this->createPostComment('Lorem ipsum comment', $post);

        $client->jsonRequest('GET', '/ajax/fetch_post_comment/'.$comment->getId());

        $this->assertStringContainsString('kbin-comment', $client->getResponse()->getContent());
        $this->assertStringContainsString('Lorem ipsum comment', $client->getResponse()->getContent());
    }

    public function testFetchUserPopup(): void
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');

        $client->jsonRequest('GET', '/ajax/fetch_user_popup/JaneDoe');

        $this->assertStringContainsString('kbin-user-popup', $client->getResponse()->getContent());
    }

    public function testNotificationCount(): void
    {
        $client = $this->createClient();
        $client->loginUser($owner = $this->getUserByUsername('owner'));

        $actor = $this->getUserByUsername('actor');

        (static::getContainer()->get(MagazineManager::class))->subscribe($this->getMagazineByName('acme'), $actor);

        $this->createEntry('test', $this->getMagazineByName('acme'), $owner);
        $entry = $this->createEntry('test', $this->getMagazineByName('acme'), $actor);

        $comment = $this->createEntryComment('test', $entry, $owner);

        $this->createPost('test', $this->getMagazineByName('acme'), $owner);
        $post = $this->createPost('test', $this->getMagazineByName('acme'), $actor);

        $reply = $this->createPostComment('test', $post, $owner);

        $client->restart();
        $client->loginUser($this->getUserByUsername('actor'));

        $client->jsonRequest('GET', '/ajax/fetch_user_notifications_count/actor');

        $this->assertStringContainsString('"count":4', $client->getResponse()->getContent());
    }
}
