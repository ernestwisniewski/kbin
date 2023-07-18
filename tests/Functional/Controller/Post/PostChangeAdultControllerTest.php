<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;

class PostChangeAdultControllerTest extends WebTestCase
{
    public function testModCanMarkAsAdultContent(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/-/moderate");
        $client->submit(
            $crawler->filter('.moderate-panel')->selectButton('18+ / nsfw')->form([
                'adult' => true,
            ])
        );
        $client->followRedirect();
        $this->assertSelectorTextContains('#main .post .badge', '18+');

        $client->submit(
            $crawler->filter('.moderate-panel')->selectButton('18+ / nsfw')->form([
                'adult' => false,
            ])
        );
        $client->followRedirect();
        $this->assertSelectorTextNotContains('#main .post', '18+');
    }
}
