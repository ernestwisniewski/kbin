<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post\Comment;

use App\Tests\WebTestCase;

class PostCommentCreateControllerTest extends WebTestCase
{
    public function testUserCanCreatePostComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');

        $crawler = $client->request('GET', '/m/acme/p/'.$post->getId().'/test-post-1');

        $client->submit(
            $crawler->filter('form[name=post_comment]')->selectButton('Add comment')->form(
                [
                    'post_comment[body]' => 'test comment 1',
                ]
            )
        );

        $this->assertResponseRedirects('/m/acme/p/'.$post->getId().'/test-post-1');
        $client->followRedirect();

        $this->assertSelectorTextContains('#main .comments', 'test comment 1');
    }

    public function testUserCannotCreateInvalidPostComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');

        $crawler = $client->request('GET', '/m/acme/p/'.$post->getId().'/test-post-1');

        $client->submit(
            $crawler->filter('form[name=post_comment]')->selectButton('Add comment')->form(
                [
                    'post_comment[body]' => 't',
                ]
            )
        );

        $this->assertSelectorTextContains('#content', 'This value is too short. It should have 2 characters or more.');
    }
}
