<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;

class PostEditControllerTest extends WebTestCase
{
    public function testAuthorCanEditOwnPost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');
        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $crawler = $client->click($crawler->filter('#main .post')->selectLink('edit')->link());

        $this->assertSelectorExists('#main .post');
        $this->assertSelectorTextContains('#post_body', 'test post 1');
//        $this->assertEquals('disabled', $crawler->filter('#post_magazine_autocomplete')->attr('disabled')); @todo

        $client->submit(
            $crawler->filter('form[name=post]')->selectButton('Edit post')->form(
                [
                    'post[body]' => 'test post 2 body',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .post .content', 'test post 2 body');
    }
}
