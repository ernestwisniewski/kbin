<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Entity\Contracts\VotableInterface;
use App\Kbin\Favourite\FavouriteToggle;
use App\Kbin\Vote\VoteCreate;
use App\Tests\WebTestCase;

class PostSingleControllerTest extends WebTestCase
{
    public function testUserCanGoToPostFromFrontpage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->createPost('test post 1');

        $crawler = $client->request('GET', '/microblog');
        $client->click($crawler->filter('.link-muted')->link());

        $this->assertSelectorTextContains('blockquote', 'test post 1');
        $this->assertSelectorTextContains('#main', 'No comments');
        $this->assertSelectorTextContains('#sidebar .magazine', 'Magazine');
        $this->assertSelectorTextContains('#sidebar .user-list', 'Moderators');
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

        $voteCreate = $this->getService(VoteCreate::class);
        $voteCreate(VotableInterface::VOTE_DOWN, $post, $this->getUserByUsername('JaneDoe'));

        $favouriteToggle = $this->getService(FavouriteToggle::class);
        $favouriteToggle($this->getUserByUsername('JohnDoe'), $post);
        $favouriteToggle($this->getUserByUsername('JaneDoe'), $post);

        $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $this->assertSelectorTextContains('.options-activity', 'Activity (2)');
    }
}
