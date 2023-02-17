<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Service\FavouriteManager;
use App\Tests\WebTestCase;

class PostFavouriteControllerTest extends WebTestCase
{
    public function testUserCanSeeVoters(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');

        $manager = $client->getContainer()->get(FavouriteManager::class);
        $manager->toggle($this->getUserByUsername('JaneDoe'), $post);
        $manager->toggle($this->getUserByUsername('JohnDoe'), $post);

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $client->click($crawler->filter('.options-activity')->selectLink('favourites (2)')->link());

        $this->assertSelectorTextContains('#main .user-list', 'JaneDoe');
        $this->assertSelectorTextContains('#main .user-list', 'JohnDoe');
    }
}
