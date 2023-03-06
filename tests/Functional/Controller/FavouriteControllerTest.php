<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;

class FavouriteControllerTest extends WebTestCase
{
    public function testLoggedUserCanAddToFavouritesEntry(): void
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

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->submit(
            $crawler->filter('#main .entry')->selectButton('favourites')->form([])
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .entry', 'favourites (1)');
    }

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

    public function testLoggedUserAddToFavouritesPost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1', null, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $client->submit(
            $crawler->filter('#main .post')->selectButton('favourites')->form([])
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .post', 'favourites (1)');
    }

    public function testLoggedUserAddToFavouritesComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1', null, $this->getUserByUsername('JaneDoe'));
        $this->createPostComment('test comment 1', $post, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $client->submit(
            $crawler->filter('#main .post-comment')->selectButton('favourites')->form([])
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .post-comment', 'favourites (1)');
    }
}

