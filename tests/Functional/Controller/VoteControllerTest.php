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

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $u1 = $this->getUserByUsername('JohnDoe');
        $u2 = $this->getUserByUsername('JaneDoe');

        $this->createVote(1, $entry, $u1);
        $this->createVote(1, $entry, $u2);

        $client->request('GET', '/');
        $crawler = $client->request('GET', '/m/acme/t/'.$entry->getId().'/-/comments');

        $this->assertUpDownVoteActions($client, $crawler);
    }

    public function testXmlUserCanVoteOnEntry(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('Actor'));

        $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('GET', '/');
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->click($crawler->filter('.entry .vote__up')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
    }

    public function testUserCanVoteOnEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('Actor'));

        $comment = $this->createEntryComment('test entry comment 1');

        $u1 = $this->getUserByUsername('JohnDoe');
        $u2 = $this->getUserByUsername('JaneDoe');

        $this->createVote(1, $comment, $u1);
        $this->createVote(1, $comment, $u2);

        $client->request('GET', '/');
        $crawler = $client->request('GET', '/m/acme/t/'.$comment->entry->getId().'/-/comments');

        $this->assertUpDownVoteActions($client, $crawler, '.comment');
    }

    public function testXmlUserCanVoteOnEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('Actor'));

        $comment = $this->createEntryComment('test entry comment 1');

        $crawler = $client->request('GET', '/m/acme/t/'.$comment->entry->getId().'/-/comments');
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->click($crawler->filter('.entry-comment .vote__up')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
    }

    private function assertUpDownVoteActions($client, $crawler, string $selector = ''): void
    {
        $this->assertSelectorTextContains($selector.' .vote__up', '2');
        $this->assertSelectorTextContains($selector.' .vote__down', '0');

        $client->click($crawler->filter($selector.' .vote__up')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($selector.' .vote__up', '3');
        $this->assertSelectorTextContains($selector.' .vote__down', '0');

        $client->click($crawler->filter($selector.' .vote__down')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($selector.' .vote__up', '2');
        $this->assertSelectorTextContains($selector.' .vote__down', '1');

        $client->click($crawler->filter($selector.' .vote__down')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($selector.' .vote__up', '2');
        $this->assertSelectorTextContains($selector.' .vote__down', '0');

        $client->submit($crawler->filter($selector.' .vote__up')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($selector.' .vote__up', '3');
        $this->assertSelectorTextContains($selector.' .vote__down', '0');

        $client->submit($crawler->filter($selector.' .vote__up')->form());
        $client->followRedirect();

        $this->assertSelectorTextContains($selector.' .vote__up', '2');
        $this->assertSelectorTextContains($selector.' .vote__down', '0');
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

    public function testXmlUserCanVoteOnPost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('Actor'));

        $this->createPost('test post 1');

        $crawler = $client->request('GET', '/microblog');
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->click($crawler->filter('.post .vote__up')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
    }

    public function testUserCanVoteOnPostComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('Actor'));

        $comment = $this->createPostComment('test post comment 1');

        $u1 = $this->getUserByUsername('JohnDoe');
        $u2 = $this->getUserByUsername('JaneDoe');

        $this->createVote(1, $comment, $u1);
        $this->createVote(1, $comment, $u2);

        $crawler = $client->request('GET', '/m/acme/p/'.$comment->post->getId());

        $this->assertUpVoteActions($client, $crawler, '.comment');
    }

    public function testXmlUserCanVoteOnPostComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('Actor'));

        $comment = $this->createPostComment('test post comment 1');

        $crawler = $client->request('GET', '/m/acme/p/'.$comment->post->getId().'/-');
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->click($crawler->filter('.post-comment .vote__up')->form());

        $this->assertStringContainsString('{"html":', $client->getResponse()->getContent());
    }

    private function assertUpVoteActions($client, $crawler, string $selector = ''): void
    {
        $this->assertSelectorTextContains($selector.' .vote__up', '2');

        $client->submit($crawler->filter($selector.' .vote__up')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($selector.' .vote__up', '3');

        $client->submit($crawler->filter($selector.' .vote__up')->form());
        $client->followRedirect();

        $this->assertSelectorTextContains($selector.' .vote__up', '2');
    }
}
