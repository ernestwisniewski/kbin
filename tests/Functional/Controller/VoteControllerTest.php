<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;

class VoteControllerTest extends WebTestCase
{
    public function testUserCanVoteOnEntry(): void
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('Actor'));

        $entry = $this->getEntryByTitle('test entry 1');

        $u1 = $this->getUserByUsername('JohnDoe');
        $u2 = $this->getUserByUsername('JaneDoe');

        $this->createVote(1, $entry, $u1);
        $this->createVote(1, $entry, $u2);

        $client->request('GET', '/');
        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId().'/-/komentarze');

        $this->assertUpDownVoteActions($client, $crawler);
    }

    public function testUserCanVoteOnPost(): void
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('Actor'));

        $post = $this->createPost('test post 1');

        $u1 = $this->getUserByUsername('JohnDoe');
        $u2 = $this->getUserByUsername('JaneDoe');

        $this->createVote(1, $post, $u1);
        $this->createVote(1, $post, $u2);

        $crawler = $client->request('GET', '/m/acme/p/'.$post->getId().'/-/comments');

        $this->assertUpVoteActions($client, $crawler);
    }

    private function assertUpDownVoteActions($client, $crawler, string $parentClass = ''): void
    {
        $this->assertSelectorTextContains($parentClass.' .vote__up', '2');
        $this->assertSelectorTextContains($parentClass.' .vote__down', '0');

        $client->click($crawler->filter($parentClass.' .vote__up')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .vote__up', '3');
        $this->assertSelectorTextContains($parentClass.' .vote__down', '0');

        $client->click($crawler->filter($parentClass.' .vote__down')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .vote__up', '2');
        $this->assertSelectorTextContains($parentClass.' .vote__down', '1');

        $client->click($crawler->filter($parentClass.' .vote__down')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .vote__up', '2');
        $this->assertSelectorTextContains($parentClass.' .vote__down', '0');

        $client->submit($crawler->filter($parentClass.' .vote__up')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .vote__up', '3');
        $this->assertSelectorTextContains($parentClass.' .vote__down', '0');

        $client->submit($crawler->filter($parentClass.' .vote__up')->form());
        $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .vote__up', '2');
        $this->assertSelectorTextContains($parentClass.' .vote__down', '0');
    }

    private function assertUpVoteActions($client, $crawler, string $parentClass = ''): void
    {
        $this->assertSelectorTextContains($parentClass.' .vote__up', '2');

        $client->submit($crawler->filter($parentClass.' .vote__up')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .vote__up', '3');

        $client->submit($crawler->filter($parentClass.' .vote__up')->form());
        $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .vote__up', '2');
    }
}
