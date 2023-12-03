<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Profile;

use App\Kbin\Magazine\MagazineSubscribe;
use App\Tests\WebTestCase;

class UserNotificationControllerTest extends WebTestCase
{
    public function testUserReceiveNotificationTest(): void
    {
        $client = $this->createClient();
        $client->loginUser($owner = $this->getUserByUsername('owner'));

        $actor = $this->getUserByUsername('actor');

        ($this->getService(MagazineSubscribe::class))($this->getMagazineByName('acme'), $owner);
        ($this->getService(MagazineSubscribe::class))($this->getMagazineByName('acme'), $actor);

        $this->loadNotificationsFixture();

        $crawler = $client->request('GET', '/settings/notifications');

        $this->assertCount(2, $crawler->filter('#main .notification'));

        $client->restart();
        $client->loginUser($actor);

        $crawler = $client->request('GET', '/settings/notifications');

        $this->assertCount(3, $crawler->filter('#main .notification'));

        $client->restart();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $crawler = $client->request('GET', '/settings/notifications');
        $this->assertCount(2, $crawler->filter('#main .notification'));
    }

    public function testCanReadAllNotifications(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        ($this->getService(MagazineSubscribe::class))(
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('owner')
        );
        ($this->getService(MagazineSubscribe::class))(
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('actor')
        );

        $this->loadNotificationsFixture();

        $client->loginUser($this->getUserByUsername('owner'));

        $crawler = $client->request('GET', '/settings/notifications');

        $this->assertCount(2, $crawler->filter('#main .notification'));
        $this->assertCount(0, $crawler->filter('#main .notification.opacity-50'));

        $client->submit($crawler->selectButton('Read all')->form());

        $crawler = $client->followRedirect();

        $this->assertCount(2, $crawler->filter('#main .notification.opacity-50'));
    }

    public function testUserCanDeleteAllNotifications(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        ($this->getService(MagazineSubscribe::class))(
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('owner')
        );
        ($this->getService(MagazineSubscribe::class))(
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('actor')
        );

        $this->loadNotificationsFixture();

        $client->loginUser($this->getUserByUsername('owner'));

        $crawler = $client->request('GET', '/settings/notifications');

        $this->assertCount(2, $crawler->filter('#main .notification'));

        $client->submit($crawler->selectButton('Purge')->form());

        $crawler = $client->followRedirect();

        $this->assertCount(0, $crawler->filter('#main .notification'));
    }
}
