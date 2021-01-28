<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Contracts\Votable;
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

        $this->createEntryVote(1, $entry, $u1);
        $this->createEntryVote(1, $entry, $u2);

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', '/m/polityka/t/'.$entry->getId());

        $this->assertVoteActions($client, $crawler);
    }

    public function testCanAddAndRemoveEntryCommentVote()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $u1 = $this->getUserByUsername('testUser1');
        $u2 = $this->getUserByUsername('testUser2');

        $entry   = $this->getEntryByTitle('testowy wpis');
        $comment = $this->createEntryComment('testowy komentarz', $entry, $u1);

        $this->createEntryCommentVote(1, $comment, $u1);
        $this->createEntryCommentVote(1, $comment, $u2);

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', '/m/polityka/t/'.$entry->getId());

        $this->assertVoteActions($client, $crawler, '.kbin-comment-list');
    }

    private function assertVoteActions( $client, $crawler, string $parentClass = '')
    {
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '2');

        $crawler = $client->submit($crawler->filter($parentClass.' .kbin-vote-uv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '3');

        $crawler = $client->submit($crawler->filter($parentClass.' .kbin-vote-dv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '2');
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-dv', '1');

        $crawler = $client->submit($crawler->filter($parentClass.' .kbin-vote-dv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '2');
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-dv', '0');

        $crawler = $client->submit($crawler->filter($parentClass.' .kbin-vote-uv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '3');
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-dv', '0');

        $crawler = $client->submit($crawler->filter($parentClass.' .kbin-vote-uv form')->form());
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains($parentClass.' .kbin-vote-uv', '2');
        $this->assertSelectorTextContains($parentClass.' .kbin-vote-dv', '0');
    }
}
