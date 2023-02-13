<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Entity\Contracts\VoteInterface;
use App\Service\VoteManager;
use App\Tests\WebTestCase;

class PostVotersControllerTest extends WebTestCase
{
    public function testUserCanSeeVoters(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $post = $this->createPost('test post 1');

        $manager = static::getContainer()->get(VoteManager::class);
        $manager->vote(VoteInterface::VOTE_UP, $post, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $client->click($crawler->filter('.kbin-options-activity')->selectLink('up votes (1)')->link());

        $this->assertSelectorTextContains('#kbin-main .kbin-user-list', 'JaneDoe');
    }
}
