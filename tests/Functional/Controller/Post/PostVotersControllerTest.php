<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Entity\Contracts\VotableInterface;
use App\Service\VoteManager;
use App\Tests\WebTestCase;

class PostVotersControllerTest extends WebTestCase
{
    public function testUserCanSeeVoters(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');

        $manager = $client->getContainer()->get(VoteManager::class);
        $manager->vote(VotableInterface::VOTE_UP, $post, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $client->click($crawler->filter('.options-activity')->selectLink('boosts (1)')->link());

        $this->assertSelectorTextContains('#main .users-columns', 'JaneDoe');
    }
}
