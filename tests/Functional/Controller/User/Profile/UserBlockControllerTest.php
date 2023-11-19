<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Profile;

use App\Kbin\Domain\DomainBlock;
use App\Kbin\Magazine\MagazineBlock;
use App\Kbin\User\UserBlock;
use App\Tests\WebTestCase;

class UserBlockControllerTest extends WebTestCase
{
    public function testUserCanSeeBlockedMagazines()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JaneDoe'));
        $magazine = $this->getMagazineByName('acme');

        ($this->getService(MagazineBlock::class))($magazine, $user);

        $crawler = $client->request('GET', '/settings/blocked/magazines');
        $client->click($crawler->filter('#main .pills')->selectLink('Magazines')->link());

        $this->assertSelectorTextContains('#main .pills .active', 'Magazines');
        $this->assertSelectorTextContains('#main .magazines', 'acme');
    }

    public function testUserCanSeeBlockedUsers()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JaneDoe'));

        ($this->getService(UserBlock::class))($user, $this->getUserByUsername('JohnDoe'));

        $crawler = $client->request('GET', '/settings/blocked/people');
        $client->click($crawler->filter('#main .pills')->selectLink('People')->link());

        $this->assertSelectorTextContains('#main .pills .active', 'People');
        $this->assertSelectorTextContains('#main .users', 'JohnDoe');
    }

    public function testUserCanSeeBlockedDomains()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test1', 'https://kbin.pub');

        ($this->getService(DomainBlock::class))($entry->domain, $user);

        $crawler = $client->request('GET', '/settings/blocked/domains');
        $client->click($crawler->filter('#main .pills')->selectLink('Domains')->link());

        $this->assertSelectorTextContains('#main .pills .active', 'Domains');
        $this->assertSelectorTextContains('#main', 'kbin.pub');
    }
}
