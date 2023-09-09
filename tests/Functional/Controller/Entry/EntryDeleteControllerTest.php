<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Tests\WebTestCase;

class EntryDeleteControllerTest extends WebTestCase
{
    public function testUserCanDeleteEntry()
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $entry = $this->getEntryByTitle('deletion test', body: 'will be deleted', magazine: $magazine, user: $user);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/m/acme');

        $this->assertSelectorExists('form[action$="delete"]');
        $client->submit(
            $crawler->filter('form[action$="delete"]')->selectButton('delete')->form()
        );

        $this->assertResponseRedirects();
    }

    public function testUserCanSoftDeleteEntry()
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $entry = $this->getEntryByTitle('deletion test', body: 'will be deleted', magazine: $magazine, user: $user);
        $comment = $this->createEntryComment('only softly', $entry, $user);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/m/acme');

        $this->assertSelectorExists('form[action$="delete"]');
        $client->submit(
            $crawler->filter('form[action$="delete"]')->selectButton('delete')->form()
        );

        $this->assertResponseRedirects();
        $client->request('GET', "/m/acme/t/{$entry->getId()}/deletion-test");

        $this->assertSelectorTextContains("#entry-{$entry->getId()} header", 'deleted_by_author');
    }
}
