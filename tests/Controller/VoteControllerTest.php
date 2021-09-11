<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class VoteControllerTest extends WebTestCase
{
    public function testCanAddAndRemoveEntryVote()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $entry = $this->getEntryByTitle('testowy wpis');

        $u1 = $this->getUserByUsername('testUser1');
        $u2 = $this->getUserByUsername('testUser2');

        $this->createVote(1, $entry, $u1);
        $this->createVote(1, $entry, $u2);

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', '/m/polityka/t/'.$entry->getId().'/-/komentarze');

        $this->assertVoteActions($client, $crawler);
    }

    private function assertVoteActions($client, $crawler, string $parentClass = '', $upVoteOnly = false)
    {
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '2');

        $client->submit($crawler->filter($parentClass.' .kbin-vote-uv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '3');

        $client->submit($crawler->filter($parentClass.' .kbin-vote-dv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '2');
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-dv', '1');

        $client->submit($crawler->filter($parentClass.' .kbin-vote-dv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '2');
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-dv', '0');

        $client->submit($crawler->filter($parentClass.' .kbin-vote-uv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '3');
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-dv', '0');

        $client->submit($crawler->filter($parentClass.' .kbin-vote-uv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '2');
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-dv', '0');
    }

    public function testCanAddAndRemoveEntryCommentVote()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $u1 = $this->getUserByUsername('testUser1');
        $u2 = $this->getUserByUsername('testUser2');

        $entry   = $this->getEntryByTitle('testowy wpis');
        $comment = $this->createEntryComment('testowy komentarz', $entry, $u1);

        $this->createVote(1, $comment, $u1);
        $this->createVote(1, $comment, $u2);

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', '/m/polityka/t/'.$entry->getId().'/-/komentarze');

        $this->assertVoteActions($client, $crawler, '.kbin-comment-list');
    }

    public function testCanAddAndRemovePostVote()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('testUser'));

        $post = $this->createPost('przykladowa tresc');

        $u1 = $this->getUserByUsername('testUser1');
        $u2 = $this->getUserByUsername('testUser2');

        $this->createVote(1, $post, $u1);
        $this->createVote(1, $post, $u2);

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', '/m/polityka/w/'.$post->getId());

        $this->assertPostVoteActions($client, $crawler, '.kbin-post');
    }

    private function assertPostVoteActions($client, $crawler, string $parentClass = '', $upVoteOnly = false)
    {
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '2');

        $client->submit($crawler->filter($parentClass.' .kbin-vote-uv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '3');

        $client->submit($crawler->filter($parentClass.' .kbin-vote-uv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '2');
    }

    public function testCanAddAndRemovePostCommentVote()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('testUser'));

        $post    = $this->createPost('przykladowa tresc');
        $comment = $this->createPostComment('przykłądowy komentarz.', $post);

        $u1 = $this->getUserByUsername('testUser1');
        $u2 = $this->getUserByUsername('testUser2');

        $this->createVote(1, $comment, $u1);
        $this->createVote(1, $comment, $u2);

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', '/m/polityka/w/'.$post->getId());

        $this->assertPostVoteActions($client, $crawler, '.kbin-comment');
    }
}
