<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post\Comment;

use App\Tests\WebTestCase;

class PostCommentFavouriteControllerTest extends WebTestCase
{
    public function testLoggedUserAddToFavouritesComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1', null, $this->getUserByUsername('JaneDoe'));
        $this->createPostComment('test comment 1', $post, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $client->submit(
            $crawler->filter('#main .post-comment')->selectButton('favourites')->form([])
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .post-comment', 'favourites (1)');
    }
}
