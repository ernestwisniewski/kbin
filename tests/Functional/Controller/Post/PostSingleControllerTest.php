<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Entity\Contracts\VoteInterface;
use App\Service\FavouriteManager;
use App\Service\VoteManager;
use App\Tests\WebTestCase;

class PostSingleControllerTest extends WebTestCase
{
    public function testUserCanGoToPostFromFrontpage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->createPost('test post 1');

        $crawler = $client->request('GET', '/microblog');
        $client->click($crawler->selectLink('now')->link());

        $this->assertSelectorTextContains('blockquote', 'test post 1');
        $this->assertSelectorTextContains('#kbin-main', 'No comments');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-magazine', 'Magazine');
        $this->assertSelectorTextContains('#kbin-sidebar .kbin-user-list', 'Moderators');
    }

    public function testUserCanSeePost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $post = $this->createPost('test post 1');

        $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $this->assertSelectorTextContains('blockquote', 'test post 1');
    }

    public function testPostActivityCounter(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $post = $this->createPost('test post 1');

        $manager = static::getContainer()->get(VoteManager::class);
        $manager->vote(VoteInterface::VOTE_DOWN, $post, $this->getUserByUsername('JaneDoe'));

        $manager = static::getContainer()->get(FavouriteManager::class);
        $manager->toggle($this->getUserByUsername('JohnDoe'), $post);
        $manager->toggle($this->getUserByUsername('JaneDoe'), $post);

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $this->assertSelectorTextContains('.kbin-options-activity', 'Activity (3)');
    }
}
