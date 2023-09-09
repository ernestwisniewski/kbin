<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentDeleteControllerTest extends WebTestCase
{
    public function testUserCanDeleteEntryComment()
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $entry = $this->getEntryByTitle('comment deletion test', body: 'a comment will be deleted', magazine: $magazine, user: $user);
        $comment = $this->createEntryComment('Delete me!', $entry, $user);
        $client->loginUser($user);

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/comment-deletion-test");

        $this->assertSelectorExists('#comments form[action$="delete"]');
        $client->submit(
            $crawler->filter('#comments form[action$="delete"]')->selectButton('delete')->form()
        );

        $this->assertResponseRedirects();
    }

    public function testUserCanSoftDeleteEntryComment()
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $entry = $this->getEntryByTitle('comment deletion test', body: 'a comment will be deleted', magazine: $magazine, user: $user);
        $comment = $this->createEntryComment('Delete me!', $entry, $user);
        $reply = $this->createEntryComment('Are you deleted yet?', $entry, $user, $comment);
        $client->loginUser($user);

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/comment-deletion-test");

        $this->assertSelectorExists("#entry-comment-{$comment->getId()} form[action$=\"delete\"]");
        $client->submit(
            $crawler->filter("#entry-comment-{$comment->getId()} form[action$=\"delete\"]")->selectButton('delete')->form()
        );

        $this->assertResponseRedirects();
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains("#entry-comment-{$comment->getId()} .content", 'deleted_by_author');
    }
}
