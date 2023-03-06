<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentFavouriteControllerTest extends WebTestCase
{
    public function testLoggedUserCanAddToFavouritesEntryComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
            null,
            null,
            $this->getUserByUsername('JaneDoe')
        );
        $this->createEntryComment('test comment 1', $entry, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->submit(
            $crawler->filter('#main .entry-comment')->selectButton('favourites')->form([])
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .entry-comment', 'favourites (1)');
    }
}
