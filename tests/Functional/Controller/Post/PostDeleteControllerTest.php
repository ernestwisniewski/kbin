<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;

class PostDeleteControllerTest extends WebTestCase
{
    public function testUserCanDeletePost()
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $post = $this->createPost('deletion test', magazine: $magazine, user: $user);
        $client->loginUser($user);

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/deletion-test");

        $this->assertSelectorExists('form[action$="delete"]');
        $client->submit(
            $crawler->filter('form[action$="delete"]')->selectButton('delete')->form()
        );

        $this->assertResponseRedirects();
    }

    public function testUserCanSoftDeletePost()
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $post = $this->createPost('deletion test', magazine: $magazine, user: $user);
        $comment = $this->createPostComment('really?', $post, $user);
        $client->loginUser($user);

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/deletion-test");

        $this->assertSelectorExists("#post-{$post->getId()} form[action$=\"delete\"]");
        $client->submit(
            $crawler->filter("#post-{$post->getId()} form[action$=\"delete\"]")->selectButton('delete')->form()
        );

        $this->assertResponseRedirects();
        $client->request('GET', "/m/acme/p/{$post->getId()}/deletion-test");
        $this->assertSelectorTextContains("#post-{$post->getId()} .content", 'deleted_by_author');
    }
}
