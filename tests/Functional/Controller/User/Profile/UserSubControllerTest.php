<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Profile;

use App\Kbin\Magazine\MagazineSubscribe;
use App\Kbin\User\UserFollow;
use App\Service\DomainManager;
use App\Tests\WebTestCase;

class UserSubControllerTest extends WebTestCase
{
    public function testUserCanSeeSubscribedMagazines()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JaneDoe'));
        $magazine = $this->getMagazineByName('acme');

        ($this->getService(MagazineSubscribe::class))($magazine, $user);

        $crawler = $client->request('GET', '/settings/subscriptions/magazines');
        $client->click($crawler->filter('#main .pills')->selectLink('Magazines')->link());

        $this->assertSelectorTextContains('#main .pills .active', 'Magazines');
        $this->assertSelectorTextContains('#main .magazines', 'acme');
    }

    public function testUserCanSeeSubscribedUsers()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JaneDoe'));

        ($this->getService(UserFollow::class))($user, $this->getUserByUsername('JohnDoe'));

        $crawler = $client->request('GET', '/settings/subscriptions/people');
        $client->click($crawler->filter('#main .pills')->selectLink('People')->link());

        $this->assertSelectorTextContains('#main .pills .active', 'People');
        $this->assertSelectorTextContains('#main .users', 'JohnDoe');
    }

    public function testUserCanSeeSubscribedDomains()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test1', 'https://kbin.pub');

        $this->getService(DomainManager::class)->subscribe($entry->domain, $user);

        $crawler = $client->request('GET', '/settings/subscriptions/domains');
        $client->click($crawler->filter('#main .pills')->selectLink('Domains')->link());

        $this->assertSelectorTextContains('#main .pills .active', 'Domains');
        $this->assertSelectorTextContains('#main', 'kbin.pub');
    }
}
