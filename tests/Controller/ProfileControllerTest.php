<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Service\MagazineManager;
use App\Tests\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ProfileControllerTest extends WebTestCase
{
    public function testUserReceiveNotifications()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        (self::$container->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $this->getUserByUsername('owner'));
        (self::$container->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $this->getUserByUsername('actor'));

        $this->loadNotificationsFixture();

        $client->loginUser($this->getUserByUsername('owner'));
        $crawler = $client->request('GET', '/profil/notyfikacje');
        $this->assertCount(4, $crawler->filter('.table-responsive tr'));

        $client->loginUser($this->getUserByUsername('actor'));
        $crawler = $client->request('GET', '/profil/notyfikacje');
        $this->assertCount(5, $crawler->filter('.table-responsive tr'));
    }

    public function testUserCanReadAllNotifications()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        (self::$container->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $this->getUserByUsername('owner'));
        (self::$container->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $this->getUserByUsername('actor'));

        $this->loadNotificationsFixture();

        $client->loginUser($this->getUserByUsername('owner'));
        $crawler = $client->request('GET', '/profil/notyfikacje');
        $crawler = $client->request('GET', '/profil/notyfikacje');

        $this->assertCount(8, $crawler->filter('.table-responsive td'));
        $this->assertCount(0, $crawler->filter('.table-responsive td.opacity-50'));

        $client->submit($crawler->selectButton('Odczytaj wszystkie')->form());

        $crawler = $client->followRedirect();

        $this->assertCount(8, $crawler->filter('.table-responsive td'));
        $this->assertCount(4, $crawler->filter('.table-responsive td.opacity-50'));
    }

    public function testUserCanDeleteAllNotifications()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        (self::$container->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $this->getUserByUsername('owner'));
        (self::$container->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $this->getUserByUsername('actor'));

        $this->loadNotificationsFixture();

        $client->loginUser($this->getUserByUsername('owner'));
        $crawler = $client->request('GET', '/profil/notyfikacje');
        $crawler = $client->request('GET', '/profil/notyfikacje');

        $this->assertCount(8, $crawler->filter('.table-responsive td'));
        $this->assertCount(0, $crawler->filter('.table-responsive td.opacity-50'));

        $client->submit($crawler->selectButton('Wyczyść')->form());

        $crawler = $client->followRedirect();

        $this->assertCount(0, $crawler->filter('.table-responsive td'));
    }
}
