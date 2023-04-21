<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Tests\WebTestCase;

class EntryChangeMagazineControllerTest extends WebTestCase
{
    public function testAdminCanChangeMagazine(): void
    {
        $client = $this->createClient();

        $user = $this->getUserByUsername('JohnDoe');
        $this->setAdmin($user);
        $client->loginUser($user);

        $this->getMagazineByName('kbin');

        $entry = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
        );

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/-/moderate");

        $client->submit(
            $crawler->filter('form[name=change_magazine]')->selectButton('change magazine')->form(
                [
                    'change_magazine[new_magazine]' => 'kbin',
                ]
            )
        );

        $client->followRedirect();
        $client->followRedirect();

        $this->assertSelectorTextContains('#header .head-title', 'kbin');
    }

    public function testUnauthorizedUserCantChangeMagazine(): void
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getMagazineByName('kbin');

        $entry = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
        );

        $client->request('GET', "/m/acme/t/{$entry->getId()}/-/moderate");

        $this->assertSelectorTextNotContains('.moderate-panel', 'change magazine');
    }
}
