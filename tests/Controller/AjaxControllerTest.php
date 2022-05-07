<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class AjaxControllerTest extends WebTestCase
{
    public function testFetchEntryArticle()
    {
        $client = $this->createClient();

        $entry = $this->getEntryByTitle('Lorem ipsum', null, 'dolor sit amet');

        $client->jsonRequest('GET', '/ajax/fetch_entry/'.$entry->getId());

        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }

    public function testFetchEntryLink()
    {
        $client = $this->createClient();

        $entry = $this->getEntryByTitle('Lorem ipsum', 'https://youtube.com');

        $client->jsonRequest('GET', '/ajax/fetch_entry/'.$entry->getId());

        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }

    public function testFetchDuplicates()
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

    public function testFetchEntryComment()
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('regularUser'));

        $comment = $this->createEntryComment('Lorem ipsum');

        $client->jsonRequest('GET', '/ajax/fetch_entry_comment/'.$comment->getId());

        $this->assertStringContainsString('kbin-comment', $client->getResponse()->getContent());
        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }

    public function testFetchPost() {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('regularUser'));

        $post = $this->createPost('Lorem ipsum');

        $client->jsonRequest('GET', '/ajax/fetch_post/'.$post->getId());

        $this->assertStringContainsString('kbin-post', $client->getResponse()->getContent());
        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }

    public function testFetchPostComment() {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('regularUser'));

        $post = $this->createPost('Lorem ipsum');

        $comment = $this->createPostComment('Lorem ipsum comment', $post);

        $client->jsonRequest('GET', '/ajax/fetch_post_comment/'.$comment->getId());

        $this->assertStringContainsString('kbin-comment', $client->getResponse()->getContent());
        $this->assertStringContainsString('Lorem ipsum comment', $client->getResponse()->getContent());
    }

    public function testFetchUserPopup() {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('regularUser'));

        $client->jsonRequest('GET', '/ajax/fetch_user_popup/regularUser');

        $this->assertStringContainsString('kbin-user-popup', $client->getResponse()->getContent());
    }
}
