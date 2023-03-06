<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Service\FavouriteManager;
use App\Tests\WebTestCase;

class PostFavouriteControllerTest extends WebTestCase
{
    public function testLoggedUserAddToFavouritesPost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1', null, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $client->submit(
            $crawler->filter('#main .post')->selectButton('favourites')->form([])
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .post', 'favourites (1)');
    }
}
