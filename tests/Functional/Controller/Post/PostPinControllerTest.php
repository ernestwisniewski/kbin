<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;

class PostPinControllerTest extends WebTestCase
{
    public function testModCanPinEntry(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost(
            'test post 1',
            $this->getMagazineByName('acme'),
        );

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/-/moderate");

        $client->submit($crawler->filter('#main .moderate-panel')->selectButton('pin')->form([]));
        $crawler = $client->followRedirect();
        $this->assertSelectorExists('#main .post .fa-thumbtack');

        $client->submit($crawler->filter('#main .moderate-panel')->selectButton('unpin')->form([]));
        $client->followRedirect();
        $this->assertSelectorNotExists('#main .post .fa-thumbtack');
    }
}
