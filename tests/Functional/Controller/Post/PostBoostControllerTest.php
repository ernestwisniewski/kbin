<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;

class PostBoostControllerTest extends WebTestCase
{
    public function testLoggedUserBoostPost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1', null, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $client->submit(
            $crawler->filter('#main .post')->selectButton('boost')->form([])
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('#main .post', 'boost (1)');

        $client->click($crawler->filter('#activity')->selectLink('boosts (1)')->link());

        $this->assertSelectorTextContains('#main .users-columns', 'JohnDoe');
    }
}
